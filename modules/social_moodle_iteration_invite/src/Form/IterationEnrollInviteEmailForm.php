<?php

namespace Drupal\social_moodle_iteration_invite\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Utility\Token;
use Drupal\file\Entity\File;
use Drupal\social_core\Form\InviteEmailBaseForm;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\social_group\SocialGroupHelperService;
use Drupal\node\NodeInterface;

/**
 * Class IterationEnrollInviteForm.
 */
class IterationEnrollInviteEmailForm extends InviteEmailBaseForm {

  /**
   * The node storage for iteration enrollments.
   *
   * @var \Drupal\Core\entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Group helper service.
   *
   * @var \Drupal\social_group\SocialGroupHelperService
   */
  protected $groupHelperService;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * The Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iteration_enroll_invite_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    EntityStorageInterface $entity_storage,
    SocialGroupHelperService $groupHelperService,
    PrivateTempStoreFactory $tempStoreFactory,
    ConfigFactoryInterface $config_factory,
    Token $token
  ) {
    parent::__construct($route_match, $entity_type_manager, $logger_factory);
    $this->entityStorage = $entity_storage;
    $this->groupHelperService = $groupHelperService;
    $this->tempStoreFactory = $tempStoreFactory;
    $this->configFactory = $config_factory;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
      $container->get('entity_type.manager')->getStorage('iteration_enrollment'),
      $container->get('social_group.helper_service'),
      $container->get('tempstore.private'),
      $container->get('config.factory'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#attributes']['class'][] = 'form--default';

    $node = $this->routeMatch->getParameter('node');

    if ($node instanceof NodeInterface) {
      $nid = $node->id();
    }

    $gid_from_entity = $this->groupHelperService->getGroupFromEntity([
      'target_type' => 'node',
      'target_id' => $nid,
    ]);

    if ($gid_from_entity !== NULL) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = $this->entityTypeManager
        ->getStorage('group')
        ->load($gid_from_entity);
    }

    $params = [
      'user' => $this->currentUser(),
      'node' => $this->routeMatch->getParameter('node'),
      'group' => $group
    ];

    // Load iteration invite configuration.
    $invite_config = $this->configFactory->get('social_moodle_iteration_invite.settings');

    // Cleanup message body and replace any links on invite preview page.
    $body = $this->token->replace($invite_config->get('invite_message'), $params);
    $body = preg_replace('/href="([^"]*)"/', 'href="#"', $body);

    // Get default logo image and replace if it overridden with email settings.
    $theme_id = $this->configFactory->get('system.theme')->get('default');
    $logo = $this->getRequest()->getBaseUrl() . theme_get_setting('logo.url', $theme_id);
    $email_logo = theme_get_setting('email_logo', $theme_id);

    if (is_array($email_logo) && !empty($email_logo)) {
      $file = File::load(reset($email_logo));

      if ($file instanceof File) {
        $logo = file_create_url($file->getFileUri());
      }
    }

    $form['email_preview'] = [
      '#type' => 'fieldset',
      '#title' => [
        'text' => [
          '#markup' => t('Preview your email invite'),
        ],
        'icon' => [
          '#markup' => '<svg class="icon icon-expand_more"><use xlink:href="#icon-expand_more" /></svg>',
          '#allowed_tags' => ['svg', 'use'],
        ],
      ],
      '#tree' => TRUE,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#attributes' => [
        'class' => [
          'form-horizontal',
          'form-preview-email',
        ],
      ],
    ];

    $form['email_preview']['preview'] = [
      '#theme' => 'invite_email_preview',
      '#logo' => $logo,
      '#subject' => $this->token->replace($invite_config->get('invite_subject'), $params),
      '#body' => $body,
      '#helper' => $this->token->replace($invite_config->get('invite_helper'), $params),
    ];

    $form['iteration'] = [
      '#type' => 'hidden',
      '#value' => $this->routeMatch->getRawParameter('node'),
    ];

    $form['actions']['submit_cancel'] = [
      '#type' => 'submit',
      '#weight' => 999,
      '#value' => $this->t('Back to iteration'),
      '#submit' => [[$this, 'cancelForm']],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  /**
   * Cancel form taking you back to an iteration.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function cancelForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('view.iteration_manage_enrollments.page_manage_enrollments', [
      'node' => $this->routeMatch->getRawParameter('node'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $nid = $form_state->getValue('iteration');

    // Check if the user is already enrolled.
    foreach ($form_state->getValue('users_fieldset')['user'] as $user) {
      // Check if the user is a filled in email.
      $email = $this->extractEmailsFrom($user);
      if ($email) {
        $conditions = [
          'field_email' => $email,
          'field_iteration' => $nid,
        ];
      }
      else {
        $conditions = [
          'field_account' => $user,
          'field_iteration' => $nid,
        ];
      }

      $enrollments = $this->entityStorage->loadByProperties($conditions);

      if (!empty($enrollments)) {
        /** @var \Drupal\social_moodle_enrollment\Entity\IterationEnrollment $enrollment */
        $enrollment = end($enrollments);
        // Of course, only delete the previous invite if it was declined
        // or if it was invalid or expired.
        $status_checks = [
          IterationEnrollmentInterface::REQUEST_OR_INVITE_DECLINED,
          IterationEnrollmentInterface::INVITE_INVALID_OR_EXPIRED,
        ];
        if (in_array($enrollment->field_request_or_invite_status->value, $status_checks)) {
          $enrollment->delete();
          unset($enrollments[$enrollment->id()]);
        }
      }

      // If enrollments can be found this user is already invited or joined.
      if (!empty($enrollments)) {
        // If the user is already enrolled, don't enroll them again.
        $form_state->unsetValue(['users_fieldset', 'user', $user]);
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $params['recipients'] = $form_state->getValue('users_fieldset')['user'];
    $params['nid'] = $form_state->getValue('iteration');
    $tempstore = $this->tempStoreFactory->get('iteration_invite_form_values');
    try {
      $tempstore->set('params', $params);

      // Create batch for sending out the invites.
      $batch = [
        'title' => $this->t('Sending invites...'),
        'init_message' => $this->t("Preparing to send invites..."),
        'operations' => [
          [
            '\Drupal\social_moodle_iteration_invite\SocialMoodleIterationInviteBulkHelper::bulkInviteUsersEmails',
            [$params['recipients'], $params['nid']],
          ],
        ],
        'finished' => '\Drupal\social_moodle_iteration_invite\SocialMoodleIterationInviteBulkHelper::bulkInviteUserEmailsFinished',
      ];
      batch_set($batch);
    }
    catch (\Exception $error) {
      $this->loggerFactory->get('iteration_invite_form_values')->alert(t('@err', ['@err' => $error]));
      $this->messenger->addWarning(t('Unable to proceed, please try again.'));
    }
  }

}
