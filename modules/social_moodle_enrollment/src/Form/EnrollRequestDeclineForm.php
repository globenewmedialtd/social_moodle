<?php

namespace Drupal\social_moodle_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\profile\Entity\Profile;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Drupal\social_moodle_enrollment\IterationEnrollmentStatusHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class EnrollRequestDeclineForm.
 *
 * @package Drupal\social_moodle_enrollment\Form
 */
class EnrollRequestDeclineForm extends FormBase {

  /**
   * The iteration enrollment entity.
   *
   * @var \Drupal\social_moodle_enrollment\Entity\IterationEnrollment
   */
  protected $iterationEnrollment;

  /**
   * The redirect destination helper.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The iteration enrollment status helper.
   *
   * @var \Drupal\social_moodle_enrollment\IterationEnrollmentStatusHelper
   */
  protected $iterationInviteStatus;

  /**
   * The full name of the user.
   *
   * @var string
   */
  protected $fullName;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $storage;

  /**
   * EnrollRequestDeclineForm constructor.
   *
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect interface.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The account interface.
   * @param \Drupal\social_moodle_enrollment\IterationEnrollmentStatusHelper $enrollmentStatusHelper
   *   The enrollment status helper.
   */
  public function __construct(RedirectDestinationInterface $redirect_destination, AccountInterface $current_user, IterationEnrollmentStatusHelper $enrollmentStatusHelper, EntityTypeManagerInterface $entity_type_manager) {
    $this->redirectDestination = $redirect_destination;
    $this->currentUser = $current_user;
    $this->iterationInviteStatus = $enrollmentStatusHelper;
    $this->storage = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('redirect.destination'),
      $container->get('current_user'),
      $container->get('social_moodle_enrollment.status_helper'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iteration_request_enrollment_decline_form';
  }

  /**
   * {@inheritdoc}
   */
  private function getCancelUrl() {
    return Url::fromUserInput($this->redirectDestination->get());
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the event_enrollment from the request.
    $this->iterationEnrollment = $this->getRequest()->get('iteration_enrollment');

   
    // Load the user profile to format a nice name.
    if (!empty($this->iterationEnrollment)) {
      $user = $this->storage->getStorage('user')->load($this->iterationEnrollment->getAccount());     
      $user_profile = $this->storage->getStorage('profile')->loadByUser($user, 'profile');
      $this->fullName = $user_profile->field_profile_first_name->value . ' ' . $user_profile->field_profile_last_name->value;
    }

    $form['#attributes']['class'][] = 'form--default';

    $form['question'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Are you sure you want to decline the enrollment request for @name?', [
        '@name' => $this->fullName,
      ]),
      '#weight' => 1,
      '#prefix' => '<div class="card"><div class="card__block">',
      '#suffix' => '</div></div>',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => [
        'class' => [
          'button',
          'button--flat',
          'btn',
          'btn-flat',
          'waves-effect',
          'waves-btn',
        ],
      ],
      '#url' => $this->getCancelUrl(),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Yes'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!empty($this->iterationEnrollment)) {
      $this->iterationEnrollment->field_request_or_invite_status->value = IterationEnrollmentInterface::REQUEST_OR_INVITE_DECLINED;
      $this->iterationEnrollment->save();
    }

    $this->messenger()->addStatus($this->t('The enrollment request of @name has been declined.', ['@name' => $this->fullName]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
