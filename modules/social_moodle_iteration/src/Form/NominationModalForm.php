<?php

namespace Drupal\social_moodle_iteration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * ModalForm class.
 */
class NominationModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_form_nomination_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $form['#prefix'] = '<div id="modal_nomination_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['nominate_users'] = array(
      '#type' => 'checkboxes',
      '#options' => array('4' => 'Paul Harris', '6' => 'Chris Hall', '11' => 'Robert Andrews'),
      '#required' => TRUE,
    );

    // A required brief_description field.
    $form['reason'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reason'),
      '#required' => TRUE,
    ];

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Application'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();


    // Create a new enrollment for the event.
    $enrollment = EventEnrollment::create($fields);
    $enrollment->save();


    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal_nomination_form', $form));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand("Success!", 'The nomination has been submitted.', ['width' => 800]));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.modal_form_nomination_modal_form'];
  }

}
