<?php

namespace Drupal\social_moodle_enrollment\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\social_moodle_enrollment\Entity\IterationEnrollment;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Drupal\group\Entity\GroupContent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class EnrollActionForm.
 *
 * @package Drupal\social_moodle_enrollment\Form
 */
class IterationEnrollActionForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The routing matcher to get the nid.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The node storage for iteration enrollments.
   *
   * @var \Drupal\Core\entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    static $count = 0;
    $count++;

    return 'iteration_enroll_action_form_' .  $count;

  }

  /**
   * Constructs an Iteration Enroll Action Form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(RouteMatchInterface $route_match, EntityStorageInterface $entity_storage, UserStorageInterface $user_storage, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser, ConfigFactoryInterface $configFactory, ModuleHandlerInterface $moduleHandler) {
    $this->routeMatch = $route_match;
    $this->entityStorage = $entity_storage;
    $this->userStorage = $user_storage;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager')->getStorage('iteration_enrollment'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    //$nid = $this->routeMatch->getRawParameter('node');
    $current_user = $this->currentUser;
    $uid = $current_user->id();

    // We check if the node is placed in a Group I am a member of. If not,
    // we are not going to build anything.
    if (!empty($nid)) {
      if (!is_object($nid) && !is_null($nid)) {
        $node = $this->entityTypeManager
          ->getStorage('node')
          ->load($nid);
      }

      $groups = $this->getGroups($node);

      // If the user is invited to an event
      // it shouldn't care about group permissions.
      $conditions = [
        'field_account' => $current_user->id(),
        'field_iteration' => $node->id(),
      ];

      $enrollments = $this->entityStorage->loadByProperties($conditions);
      

    }

    $enrollment_methods = $node->field_iteration_enrollment->referencedEntities();
    $methods = [];
    $default_methods = [
      'invite_only',
      'open_to_enroll',
      'request_to_enroll'
    ];

    if (isset($enrollment_methods)) {
      foreach ($enrollment_methods as $method) {
        if (in_array($method->id,$default_methods)) {
          $methods[$method->id] = $method->id;
        } 
      }
    } 

    $form['iteration'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    $submit_text = $this->t('Enroll');
    $to_enroll_status = '1';
    $enrollment_open = TRUE;
    $request_to_join = FALSE;
    $isNodeOwner = ($node->getOwnerId() === $uid);

    // Initialise the default attributes for the "Enroll" button
    // if the event enroll method is request to enroll, this will
    // be overwritten because of the modal.
    $attributes = [
      'class' => [
        'btn',
        'btn-accent brand-bg-accent',
        'btn-lg btn-raised',
        'waves-effect',
      ],
    ];

    // Add the enrollment closed label.
    if ($this->iterationHasBeenFinished($node)) {
      $submit_text = $this->t('Course has passed');
      $enrollment_open = FALSE;
    }

    if (!$current_user->isAnonymous()) {
      $conditions = [
        'field_account' => $uid,
        'field_iteration' => $nid,
      ];

      $enrollments = $this->entityStorage->loadByProperties($conditions);

	
      if ($enrollment = array_pop($enrollments)) {
        $current_enrollment_status = $enrollment->field_enrollment_status->value;
        if ($current_enrollment_status === '1') {
          $submit_text = $this->t('Enrolled');
          $to_enroll_status = '0';
          $enrollment_open = FALSE;
        }
        // If someone requested to join the event.
        elseif (in_array('request_to_enroll', $methods) && !$isNodeOwner) {
          $iteration_request_ajax = TRUE;
          if ((int) $enrollment->field_request_or_invite_status->value === IterationEnrollmentInterface::REQUEST_PENDING) {
            $submit_text = $this->t('Pending');
            $iteration_request_ajax = FALSE;
          }
        }
      }

      // Use the ajax submit if the enrollments are empty, or if the
      // user cancelled his enrollment and tries again.
      if ($enrollment_open === TRUE) {
        if (!$isNodeOwner && (empty($enrollment) && in_array('request_to_enroll', $methods))
          || (isset($iteration_request_ajax) && $iteration_request_ajax === TRUE)) {
          $attributes = [
            'class' => [
              'use-ajax',
              'js-form-submit',
              'form-submit',
              'btn',
              'btn-accent',
              'btn-lg',
            ],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => json_encode([
              'title' => t('Request to enroll'),
              'width' => 'auto',
            ]),
          ];
          $request_to_join = TRUE;
        }
      }
      else {

        $options_course_link = [
          'query' => [
            'idnumber' => $nid
          ]
        ];

        $form['buttons']['course_link'] = [
          '#type' => 'link',
          '#title' => $this->t('Show course'),
          '#url' => Url::fromUri('internal:/moodle/redirect.php',$options_course_link),
          '#attributes' => [
            'class' => [
              'js-form-submit',
              'form-submit',
              'btn',
              'btn-accent',
              'btn-lg',
           ]
          ]
        ];


      }
    }

    $form['to_enroll_status'] = [
      '#type' => 'hidden',
      '#value' => $to_enroll_status,
    ];

    $form['enroll_for_this_event'] = [
      '#type' => 'submit',
      '#value' => $submit_text,
      '#disabled' => !$enrollment_open,
      '#attributes' => $attributes,
    ];

    if ($request_to_join === TRUE) {
      $form['enroll_for_this_event'] = [
        '#type' => 'link',
        '#title' => $submit_text,
        '#url' => Url::fromRoute('social_moodle_enrollment.request_enroll_dialog', ['node' => $nid]),
        '#attributes' => $attributes,
      ];
    }

    $form['#attributes']['name'] = 'enroll_action_form';

    if ((isset($enrollment->field_enrollment_status->value) && $enrollment->field_enrollment_status->value === '1')
      || (isset($enrollment->field_request_or_invite_status->value)
      && (int) $enrollment->field_request_or_invite_status->value === IterationEnrollmentInterface::REQUEST_PENDING)) {
      // Extra attributes needed for when a user is logged in. This will make
      // sure the button acts like a dropwdown.
      $form['enroll_for_this_event']['#attributes'] = [
        'class' => [
          'btn',
          'btn-accent brand-bg-accent',
          'btn-lg btn-raised',
          //'dropdown-toggle',
          'waves-effect',
        ],
        'autocomplete' => 'off',
        //'data-toggle' => 'dropdown',
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
        //'data-caret' => 'true',
      ];

      $form['enroll_for_this_event']['#disabled'] = TRUE;

      //$cancel_text = $this->t('Cancel enrollment');

      // Add markup for the button so it will be a dropdown.
      //$form['feedback_user_has_enrolled'] = [
        //'#markup' => '<ul class="dropdown-menu dropdown-menu-right"><li><a href="#" class="enroll-form-submit"> ' . $cancel_text . ' </a></li></ul>',
      //];

      //$form['#attached']['library'][] = 'social_moodle_enrollment/form_submit';

    }

    return $form;
  }

  /**
   * Function to determine if an iteration has been finished.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The iteration.
   *
   * @return bool
   *   TRUE if the iteration is finished / completed.
   */
  protected function iterationHasBeenFinished(Node $node) {
    // Use the start date when the end date is not set to determine if the
    // event is closed.
    /** @var \Drupal\Core\Datetime\DrupalDateTime $check_end_date */
    $check_end_date = $node->field_iteration_date->date;
    $finished = FALSE;

    if (isset($node->field_iteration_date_end->date)) {
      $check_end_date = $node->field_iteration_date_end->date;
    }

    $current_time = new DrupalDateTime();

    // The event has finished if the end date is smaller than the current date.
    // only if there are dates given
    if (isset($node->field_iteration_date_end->date) && isset($node->field_iteration_date->date)) {
      if ($current_time > $check_end_date) {
        $finished = TRUE;
      }
    }

    return $finished;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_user = $this->currentUser;
    $uid = $current_user->id();
    $nid = $form_state->getValue('iteration');    

    $node = $this->entityTypeManager->getStorage('node')->load($nid);

    // Redirect anonymous use to login page before enrolling to an event.
    if ($current_user->isAnonymous()) {
      $node_url = Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString();
      $destination = $node_url;
      // If the request enroll method is set, alter the destination for AN.
      if ((int) $node->get('field_enroll_method')->value === IterationEnrollmentInterface::ENROLL_METHOD_REQUEST) {
        $destination = $node_url . '?requested-enrollment=TRUE';
      }
      $form_state->setRedirect('user.login', [], ['query' => ['destination' => $destination]]);

      // Check if user can register accounts.
      if ($this->configFactory->get('user.settings')->get('register') !== UserInterface::REGISTER_ADMINISTRATORS_ONLY) {
        $log_in_url = Url::fromUserInput('/user/login');
        $log_in_link = Link::fromTextAndUrl($this->t('log in'), $log_in_url)->toString();
        $create_account_url = Url::fromUserInput('/user/register');
        $create_account_link = Link::fromTextAndUrl($this->t('create a new account'), $create_account_url)->toString();
        $message = $this->t('Please @log_in or @create_account_link so that you can enroll to the course.', [
          '@log_in' => $log_in_link,
          '@create_account_link' => $create_account_link,
        ]);
      }
      else {
        $log_in_url = Url::fromUserInput('/user/login');
        $log_in_link = Link::fromTextAndUrl($this->t('log in'), $log_in_url)->toString();
        $message = $this->t('Please @log_in so that you can enroll to the event.', [
          '@log_in' => $log_in_link,
        ]);
      }

      $this->messenger()->addStatus($message);
      return;
    }

    $to_enroll_status = $form_state->getValue('to_enroll_status');

    $conditions = [
      'field_account' => $uid,
      'field_iteration' => $nid,
    ];

    $enrollments = $this->entityStorage->loadByProperties($conditions);

    // Invalidate cache for our enrollment cache tag in
    // social_event_node_view_alter().
    $cache_tag = 'iteration_enrollment:' . $nid . '-' . $uid;
    Cache::invalidateTags([$cache_tag]);

    if ($enrollment = array_pop($enrollments)) {
      $current_enrollment_status = $enrollment->field_enrollment_status->value;
      // The user is enrolled, but cancels his enrollment.
      if ($to_enroll_status === '0' && $current_enrollment_status === '1') {
        // The user is enrolled by invited or request, but either the user or
        // event manager is declining or invalidating the enrollment.
        if ($enrollment->field_request_or_invite_status
          && (int) $enrollment->field_request_or_invite_status->value === IterationEnrollmentInterface::INVITE_ACCEPTED_AND_JOINED) {
          // Mark this user his enrollment as declined.
          $enrollment->field_request_or_invite_status->value = IterationEnrollmentInterface::REQUEST_OR_INVITE_DECLINED;
          // If the user is cancelling, un-enroll.
          $current_enrollment_status = $enrollment->field_enrollment_status->value;
          if ($current_enrollment_status === '1') {
            $enrollment->field_enrollment_status->value = '0';
          }
          $enrollment->save();
        }
        // Else, the user simply wants to cancel his enrollment, so at
        // this point we can safely delete the enrollment record as well.
        else {
          $enrollment->delete();
        }
      }
      elseif ($to_enroll_status === '1' && $current_enrollment_status === '0') {
        $enrollment->field_enrollment_status->value = '1';
        $enrollment->save();
      }
      elseif ($to_enroll_status === '2' && $current_enrollment_status === '0') {
        if ((int) $enrollment->field_request_or_invite_status->value === IterationEnrollmentInterface::REQUEST_PENDING) {
          $enrollment->delete();
        }
      }

    }
    else {
      // Default event enrollment field set.
      $fields = [
        'user_id' => $uid,
        'field_iteration' => $nid,
        'field_enrollment_status' => '1',
        'field_account' => $uid,
      ];

      // If request to join is on, alter fields.
      if ($to_enroll_status === '2') {
        $fields['field_enrollment_status'] = '0';
        $fields['field_request_or_invite_status'] = IterationEnrollmentInterface::REQUEST_PENDING;
      }

      // Create a new enrollment for the event.
      $enrollment = IterationEnrollment::create($fields);
      $enrollment->save();
    }
  }

  /**
   * Get group object where event enrollment is posted in.
   *
   * Returns an array of Group Objects.
   *
   * @return array
   *   Array of group entities.
   */
  public function getGroups($node) {
    $groupcontents = GroupContent::loadByEntity($node);

    $groups = [];
    // Only react if it is actually posted inside a group.
    if (!empty($groupcontents)) {
      foreach ($groupcontents as $groupcontent) {
        /** @var \Drupal\group\Entity\GroupContent $groupcontent */
        $group = $groupcontent->getGroup();
        /** @var \Drupal\group\Entity\Group $group */
        $groups[] = $group;
      }
    }

    return $groups;
  }

}
