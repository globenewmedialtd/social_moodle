<?php

namespace Drupal\social_moodle_enrollment\Form;

use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\user\Entity\User;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;
use Drupal\social_moodle_application\Entity\Application;
use Drupal\social_moodle_application\ApplicationInterface;


/**
 * SendToDestinationsForm class.
 */
class IterationApplicationRequestModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'request_iteration_application_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $enrollment_method = $node->get('field_iteration_enrollment')->referencedEntities();


    $form['#prefix'] = '<div id="request_iteration_application">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['iteration'] = [
      '#type' => 'hidden',
      '#value' => $nid,
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
    $form['pdf'] = array(
      '#type' => 'managed_file',
      '#name' => 'pdf',
      '#title' => t('PDF'),
      '#size' => 20,
      '#description' => t('PDF format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'private://application/',
      '#required' => FALSE,
    );

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send request'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'] = [
      'core/drupal.dialog.ajax',
      'social_moodle_enrollment/modal_application',
    ];
    $form['#attached']['drupalSettings']['iterationAppliationRequest'] = [
      'closeDialog' => TRUE,
    ];

    return $form;

  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      // If there are errors, we can show the form again with the errors in
      // the status_messages section.
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $form_state->setRebuild();

      return $response->addCommand(new OpenModalDialogCommand($this->t('Request to nomination'), $form, static::getDataDialogOptions()));
    }

    // Refactor this into a service or helper.
    $message = $form_state->getValue('message');

    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $supervisor = social_moodle_enrollment_get_users_supervisor($uid);
    $nid = $form_state->getValue('iteration');
    $fid = $form_state->getValue('pdf');

    if ( isset($fid) && !empty($fid) ) {

      $fields = [
        'field_application_user' => $uid,
        'field_iteration' => $nid,
        'field_supervisor' => $supervisor,
        'field_pdf' => $fid,
        'field_state' => 'approved_supervisor',
        'field_application_type' => 'application'
      ]; 

    } 
    else {
      $fields = [
        'field_application_user' => $uid,
        'field_iteration' => $nid,
        'field_supervisor' => $supervisor,
        'field_application_type' => 'application'
      ]; 
    }

    \Drupal::logger('social_moodle_enrollment')->warning('<pre><code>' . print_r($fields, TRUE) . '</code></pre>');

    if ($supervisor) {
      $application = Application::create($fields);
      $application->setOwnerId($supervisor);
      $application->save();
    }
  

    // On success leave a message and reload the page.
    \Drupal::messenger()->addStatus(t('Your request has been sent successfully'));
    return $response->addCommand(new CloseDialogCommand());
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
    return ['config.request_iteration_application_modal_form'];
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

}
