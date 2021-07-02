<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message\Plugin\QueueWorker;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\social_moodle_iteration_enrollment_welcome_message\Entity\IterationWelcomeMessageInterface;
use Drupal\Core\Utility\Token;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupContentInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Render\Markup;
use Drupal\node\NodeInterface;
use Drupal\social_group\SocialGroupHelperService;

/**
 * Queue worker to process email to users.
 *
 * @QueueWorker(
 *   id = "iteration_welcome_message_email_queue",
 *   title = @Translation("Iteration Welcome Message email processor"),
 *   cron = {"time" = 60}
 * )
 */
class IterationWelcomeMessageMailQueueProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $storage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The language manager interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Email validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Group helper service.
   *
   * @var \Drupal\social_group\SocialGroupHelperService
   */
  protected $groupHelperService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailManagerInterface $mail_manager, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation, Connection $database, LanguageManagerInterface $language_manager, EmailValidatorInterface $email_validator, Token $token, SocialGroupHelperService $group_helper_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailManager = $mail_manager;
    $this->storage = $entity_type_manager;
    $this->connection = $database;
    $this->setStringTranslation($string_translation);
    $this->languageManager = $language_manager;
    $this->emailValidator = $email_validator;
    $this->token = $token;
    $this->groupHelperService = $group_helper_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.mail'),
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('database'),
      $container->get('language_manager'),
      $container->get('email.validator'),
      $container->get('token'),
      $container->get('social_group.helper_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Validate if the queue data is complete before processing.
    if (self::validateQueueItem($data)) {
      // Get the group and users.
      $node = $this->storage->getStorage('node')->load($data['node']);
      $users = $this->storage->getStorage('user')->loadMultiple($data['users']);

      //Check if all needed data available to process the item
      if ($node instanceof NodeInterface && !empty($users)) {
        \Drupal::logger('social_moodle_iteration_enrollment_welcome_message')->notice('successful item processed');
         // We need to get the group context
         $groupHelperService = $this->groupHelperService;
    	  // We need to get the group via groupHelperService
    	  $gid_from_entity = $groupHelperService->getGroupFromEntity([
          'target_type' => 'node',
          'target_id' => $node->id(),
        ]);
  
        if ($gid_from_entity !== NULL) {
         /** @var \Drupal\group\Entity\GroupInterface $group */
         $group = $this->storage
           ->getStorage('group')
           ->load($gid_from_entity);
        }


        /** @var \Drupal\user\UserInterface $user */
        foreach ($users as $user) {
          // Get language of user
          $user_language = $user->language();
          $iteration_welcome_message_content = $this->getIterationWelcomeMessage($node,$user_language);
          
          $conditions = [
            'field_account' => $user->id(),
            'field_iteration' => $node->id(),
          ];

          $iteration_enrollments = $this->storage->getStorage('iteration_enrollment')
               ->loadByProperties($conditions);

          $welcome_message_sent = FALSE;
    
          if ($iteration_enrollment = array_pop($iteration_enrollments)) {
            if ($iteration_enrollment->field_welcome_message->value === 1) {
              $welcome_message_sent = TRUE;
            }            
          }          
          // Attempt sending mail and send only if welcome message defined in user language
          if ($user->getEmail() && $iteration_welcome_message_content instanceof IterationWelcomeMessageInterface && !$welcome_message_sent) {
            $this->sendMail($user->getEmail(), $user->language()->getId(), $iteration_welcome_message_content, $user, $group, $node);
          }
        }
      }
      else {
        // Inform about the message not sent
        $batch_status_info = $this->t('Welcome Message not sent, because there is no Group and Users available');
        \Drupal::logger('social_moodle_iteration_enrollment_welcome_message')->notice($batch_status_info);
      }
    }
  }

  /**
   * Send the email.
   *
   * @param string $user_mail
   *   The recipient email address.
   * @param string $langcode
   *   The recipient language.
   * @param \Drupal\social_moodle_iteration_enrollment_welcome_message\Entity\IterationWelcomeMessageInterface $group_welcome_message
   *   The subject and body field from the IterationWelcomeMessage Entity
   * @param string $display_name
   *   In case of anonymous users a display name will be given.
   */
  protected function sendMail(string $user_mail, string $langcode, IterationWelcomeMessageInterface $iteration_welcome_message, $user, $group, $node, $display_name = NULL) {

    // Send Emails from the configured site mail
    $site_mail = \Drupal::config('system.site')->get('mail');

    //$token_service = \Drupal::token();
    $token_context = array(
      'user' => $user,
      'group' => $group
    );

    $subject =  PlainTextOutput::renderFromHtml($this->token->replace($iteration_welcome_message->getSubject(), $token_context));
    $body = $this->token->replace($iteration_welcome_message->getBody()['value'], $token_context);
    $body_existing = $this->token->replace($iteration_welcome_message->getBodyExisting()['value'], $token_context);

    // Load user.module from the user module.
    module_load_include('module', 'user');

    $special_token_context = ['user' => $user];

    $special_token_options = [
      'langcode' => $langcode,
      'callback' => 'user_mail_tokens',
      'clear' => TRUE,
    ];

    $subject_special_tokens = PlainTextOutput::renderFromHtml($this->token->replace($subject, $special_token_context, $special_token_options));
    $body_special_tokens = $this->token->replace($body, $special_token_context, $special_token_options);
    $body_existing_special_tokens = $this->token->replace($body_existing, $special_token_context, $special_token_options);

    if ($user->getLastLoginTime() > 0 && !empty($body_existing)) {

      $body_special_tokens = $body_existing_special_tokens;

    }
    
    $context = [
      'subject' => $subject_special_tokens,
      'message' => Markup::create($body_special_tokens),
    ];

    if ($display_name) {
      $context['display_name'] = $display_name;
    }

    // Ensure html
    $context['params'] = array('format' => 'text/html');

    // Sending Email
    $delivered = $this->mailManager->mail('system', 'action_send_email', $user_mail, $langcode, [
      'context' => $context
    ]);

    if(!$delivered) {
      \Drupal::logger('social_moodle_iteration_enrollment_welcome_message')->notice($user_mail . ' - ' . $this->t('not delivered!'));    
    }
    else {
      // Update the iterat$this->storage
      $conditions = [
        'field_account' => $user->id(),
        'field_iteration' => $node->id(),
      ];
      
      $iteration_enrollments = $this->storage->getStorage('iteration_enrollment')
           ->loadByProperties($conditions);

      if ($iteration_enrollment = array_pop($iteration_enrollments)) {
        $iteration_enrollment->field_welcome_message->value = 1;
        $iteration_enrollment->save();
      }

    }

  }

  /**
   * Check if this item is last.
   *
   * @param string $mail_id
   *   The email ID that is in the batch.
   *
   * @return int
   *   The remaining number.
   */
  protected function lastItem($mail_id) {
    // Escape the condition values.
    $item_type = $this->connection->escapeLike('mail');
    $item_id = $this->connection->escapeLike($mail_id);

    // Get all queue items from the queue worker.
    $query = $this->connection->select('queue', 'q');
    $query->fields('q', ['data', 'name']);
    // Plugin name is queue name.
    $query->condition('q.name', 'iteration_welcome_message_email_queue');
    // Add conditions for the item type and item mail id's.
    // This is not exact but an educated guess as there can be user id's in the
    // data that could contain the item id.
    $query->condition('q.data', '%' . $item_type . '%', 'LIKE');
    $query->condition('q.data', '%' . $item_id . '%', 'LIKE');
    $results = (int) $query->countQuery()->execute()->fetchField();

    // Return TRUE when last item.
    return !($results !== 1);
  }

  /**
   * Validate the queue item data.
   *
   * Before processing the queue item data we want to check if all the
   * necessary components are available.
   *
   * @param array $data
   *   The content of the queue item.
   *
   * @return bool
   *   True if the item contains all the necessary data.
   */
  private static function validateQueueItem(array $data) {
    // The queue data must contain the 'mail' key and it should either
    // contain 'users' or 'user_mail_addresses'.
    return isset($data['users']);
  }

  /**
   * Get the proper Welcome Message for the iteration
   *
   * Try to deliver a weclome message for the user language,
   * if available.
   *
   * If not deliver a welcome message for the default language
   * or False if there is no Welcome Message.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group object.
   *
   * @param string $user_language
   *   The language for the user
   *
   * @return \Drupal\social_moodle_iteration_enrollment_welcome_message\Entity\IterationWelcomeMessageInterface $iteration_welcome_message
   *   or
   * @return bool FALSE
   */
  protected function getIterationWelcomeMessage(NodeInterface $node, LanguageInterface $language) {

    $iteration_welcome_message_id = FALSE;
    $iteration_welcome_message_content = FALSE;

    $query = $this->storage->getStorage('iteration_welcome_message')->getQuery();
    $query->condition('node', $node->id());


    $query->accessCheck(FALSE);

    $ids = $query->execute();

    if (!empty($ids)) {

      $iteration_welcome_messages = $this->storage->getStorage('iteration_welcome_message')->loadMultiple($ids);

      foreach ($iteration_welcome_messages as $iteration_welcome_message) {
        if ($iteration_welcome_message->getNode() === $node->id()) {
          $iteration_welcome_message_id = $iteration_welcome_message->id();
        }
      }

      if ($iteration_welcome_message_id) {
        // Load our entity in users language or default
        $iteration_welcome_message = $this->storage->getStorage('iteration_welcome_message')
          ->load($iteration_welcome_message_id);
        $iteration_welcome_message_content = $this->getTranslatedConfigEntity($iteration_welcome_message,$language);


        if (!$iteration_welcome_message_content instanceof IterationWelcomeMessageInterface) {
          // Load default language
          $iteration_welcome_message_content = $this->storage->getStorage('iteration_welcome_message')
            ->load($iteration_welcome_message_id);
        }
      }
    }

    return $iteration_welcome_message_content;

  }

  /**
   * Get the translated Welcome Message for the iteration
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $configEntity
   *   The group object.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language interface for the user
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface $translatedConfigEntity
   */
  protected function getTranslatedConfigEntity(ConfigEntityInterface $configEntity, LanguageInterface $language) {
    $currentLanguage = $this->languageManager->getConfigOverrideLanguage();
    $this->languageManager->setConfigOverrideLanguage($language);
    $translatedConfigEntity = $this->storage
      ->getStorage($configEntity->getEntityTypeId())
      ->load($configEntity->id());
      $this->languageManager->setConfigOverrideLanguage($currentLanguage);

    return $translatedConfigEntity;
  }


}
