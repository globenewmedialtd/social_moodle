<?php

namespace Drupal\social_moodle_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * ModalForm class.
 */
class ApplicationModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_form_application_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {



    $form['#prefix'] = '<div id="modal_application_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    // A required brief_description field.
    $form['brief_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Brief description of duties and responsibilities'),
      '#required' => TRUE,
    ];

    // A required brief_description field.
    $form['contribution'] = [
      '#type' => 'textarea',
      '#title' => $this->t('How will this training contribute to your work/Units or Departments work?'),
      '#required' => TRUE,
    ];

    $form['file_upload_details'] = array(
      '#markup' => t('<b>Supervisor confirmation</b>'),
    );
	
    $validators = array(
      'file_validate_extensions' => array('pdf'),
    );
    $form['my_file'] = array(
      '#type' => 'managed_file',
      '#name' => 'my_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('PDF format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'private://application/',
      '#required' => TRUE,
    );

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

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal_application_form', $form));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand("Success!", 'The modal form has been submitted.', ['width' => 800]));
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
    return ['config.modal_form_application_modal_form'];
  }

}
