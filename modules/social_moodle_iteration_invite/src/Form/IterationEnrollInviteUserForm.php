<?php

namespace Drupal\social_moodle_iteration_invite\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\social_core\Form\InviteUserBaseForm;
use Drupal\social_moodle_enrollment\IterationEnrollmentStatusHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IterationEnrollInviteForm.
 */
class IterationEnrollInviteUserForm extends InviteUserBaseForm {


  /**
   * The iteration invite status helper.
   *
   * @var \Drupal\social_moodle_enrollment\IterationEnrollmentStatusHelper
   */
  protected $iterationInviteStatus;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory, IterationEnrollmentStatusHelper $iterationInviteStatus, PrivateTempStoreFactory $tempStoreFactory) {
    parent::__construct($route_match, $entity_type_manager, $logger_factory);
    $this->iterationInviteStatus = $iterationInviteStatus;
    $this->tempStoreFactory = $tempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
      $container->get('social_moodle_enrollment.status_helper'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iteration_enroll_invite_user_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $nid = $this->routeMatch->getRawParameter('node');

    $form['iteration'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    $form['name'] = [
      '#type' => 'social_enrollment_entity_autocomplete',
      '#selection_handler' => 'social',
      '#selection_settings' => [],
      '#target_type' => 'user',
      '#tags' => TRUE,
      '#description' => $this->t('To add multiple members, separate each member with a comma ( , ).'),
      '#title' => $this->t('Select members to add by name or email address'),
      '#weight' => -1,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $params['recipients'] = $form_state->getValue('entity_id_new');
    $params['nid'] = $form_state->getValue('iteration');
    $tempstore = $this->tempStoreFactory->get('iteration_invite_form_values');
    try {
      $tempstore->set('params', $params);
      $form_state->setRedirect('social_moodle_iteration_invite.confirm_invite', ['node' => $form_state->getValue('iteration')]);
    }
    catch (\Exception $error) {
      $this->loggerFactory->get('iteration_invite_form_values')->alert(t('@err', ['@err' => $error]));
      $this->messenger->addWarning(t('Unable to proceed, please try again.'));
    }
  }

}
