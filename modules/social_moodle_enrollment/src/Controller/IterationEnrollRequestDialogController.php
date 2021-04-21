<?php

namespace Drupal\social_moodle_enrollment\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\node\NodeInterface;

/**
 * Contains methods for the modal form when requesting to enroll in an iteration.
 *
 * @package Drupal\social_moodle_enrollment\Controller
 */
class IterationEnrollRequestDialogController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The ModalFormExampleController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(FormBuilder $formBuilder, AccountProxyInterface $currentUser) {
    $this->formBuilder = $formBuilder;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('current_user')
    );
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'dialogClass' => 'form--default social_moodle_enrollment-popup',
      'closeOnEscape' => TRUE,
      'width' => '582',
    ];
  }

  /**
   * Enroll dialog callback.
   */
  public function enrollDialog() {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $form = $this->formBuilder->getForm('Drupal\social_moodle_enrollment\Form\IterationEnrollRequestModalForm');

    if ($this->currentUser()->isAnonymous()) {
      $form = $this->formBuilder->getForm('Drupal\social_moodle_enrollment\Form\IterationEnrollRequestAnonymousForm');
      $response->addCommand(new OpenModalDialogCommand($this->t('Request to enroll'), $form, [
        'width' => '337px',
        'closeOnEscape' => TRUE,
        'dialogClass' => 'social_moodle_enrollment-popup social_event-popup--anonymous',
      ]));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand($this->t('Request to enroll'), $form, static::getDataDialogOptions()));
    }

    return $response;
  }

  /**
   * The _title_callback for the iteration enroll dialog route.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return string
   *   The page title.
   */
  public function enrollTitle(NodeInterface $node) {
    return $this->t('Request enrollment in @label Iteration', ['@label' => $node->label()]);
  }

  /**
   * Determines if user has access to enroll form.
   */
  public function enrollAccess(NodeInterface $node) {
    return AccessResult::allowed();
  }

}
