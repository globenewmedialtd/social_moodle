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
use Drupal\Core\Url;


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
    $gid = $this->getGroupId($node);
    //$enrollment_method = $node->get('field_iteration_enrollment')->referencedEntities();

    $current_user = \Drupal::currentUser();    
    //$supervisor = User::load($current_user->id())->hasRole('supervisor');
    $supervisor = social_moodle_enrollment_get_users_supervisor($current_user->id());
    $upload_required = TRUE;
    if (isset($supervisor) && ($supervisor)) {
      $upload_required = FAlSE;
    }
    
    $supervisor_name = $this->getSupervisorName($supervisor);
    $approval_options = [
      'upload' => $this->t('Upload for approval'),
      'send' => $this->t('Send for approval to') . ' ' . $supervisor_name,
    ];


    $form['#prefix'] = '<div id="request_iteration_application">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    if (isset($supervisor) && ($supervisor)) {
      $form['supervisor_name'] = [
        '#markup' => '<p>' . $this->t('Your Supervisor is') . ' ' . $supervisor_name . '</p>',
      ];
    }

    $form['iteration'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    $form['group'] = [
      '#type' => 'hidden',
      '#value' => $gid,
    ];

    // A required duties field.
    $form['duties'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Brief description of duties and responsibilities'),
      '#required' => TRUE,
    ];

    // A required reason field.
    $form['reason'] = [
      '#type' => 'textarea',
      '#title' => $this->t('How will this training contribute to your work/Units or Departments work?'),
      '#required' => TRUE,
    ];

    if (isset($supervisor) && ($supervisor)) {

      $form['approval_options'] = [
        '#type' => 'radios',
        '#title' => $this->t('Approval'),
        '#options' => $approval_options,
        '#default_value' => 'upload',
        '#attributes' => [
          'name' => 'approval_options',
        ],
        '#ajax' => [
          // #ajax has two required keys: callback and wrapper.
          // 'callback' is a function that will be called when this element
          // changes.
          'callback' => '::ajaxShowFileUploadCallback',
          // 'wrapper' is the HTML id of the page element that will be replaced.
          'wrapper' => 'replace-file-upload-container',
        ]
      ];






      // The 'replace-textfield-container' container will be replaced whenever
      // 'changethis' is updated.
      $form['replace_file_upload_container'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'replace-file-upload-container'],
      ];

    }

    $validators = array(
      'file_validate_extensions' => array('pdf'),
    );

    if (isset($supervisor) && (!$supervisor)) {


      $form['pdf_info'] = [
        '#markup' => '<span>' . $this->t('A supervisor confirmation is required to apply for trainings. Please download and reattach this template') . ': ' . '</span>'
      ];

      $form['download_template'] = [
        '#title' => 'Download Template',
        '#type' => 'link',
        '#url' => Url::fromRoute('social_moodle_enrollment.application_download_file'),
        '#attributes' => ['class' => ['form-submit','btn','btn-accent','btn-sm']]
      ];


    }
    else {

      $form['replace_file_upload_container']['pdf_info'] = [
        '#markup' => '<span>' . $this->t('A supervisor confirmation is required to apply for trainings. Please download and reattach this template') . ': ' . '</span>'
      ];
  
      $form['replace_file_upload_container']['download_template'] = [
        '#title' => 'Download Template',
        '#type' => 'link',
        '#url' => Url::fromRoute('social_moodle_enrollment.application_download_file'),
        '#attributes' => ['class' => ['form-submit','btn','btn-accent','btn-sm']]
      ];

    }




    $form['replace_file_upload_container']['pdf'] = array(
      '#type' => 'managed_file',
      '#title' => t('PDF'),
      '#size' => 20,
      '#description' => t('PDF format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'private://application/',
      '#required' => $upload_required,
    );

    // AJAX Request get value.
    // We can change how we build the form based on $form_state.
    if (isset($supervisor) && ($supervisor)) {
      $show_pdf = $form_state->getValue('approval_options');
      if ($show_pdf !== NULL && $show_pdf === 'send') {
        $form['replace_file_upload_container']['pdf']['#access'] = FALSE;
        $form['replace_file_upload_container']['pdf_info']['#access'] = FALSE;
        $form['replace_file_upload_container']['download_template']['#access'] = FALSE;
     }
    }

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
      'core/jquery.form',  
      'core/drupal.ajax',    
      'core/drupal.dialog.ajax',      
      'social_moodle_enrollment/modal',
    ];
    $form['#attached']['drupalSettings']['iterationModalRequest'] = [
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
    $gid = $form_state->getValue('group');
    $fid = $form_state->getValue('pdf');
    $reason = $form_state->getValue('reason');
    $duties = $form_state->getValue('duties');
    

    if ( isset($fid) && !empty($fid) ) {

      $fields = [
        'field_iteration' => $nid,
        'field_group' => $gid,
        //'field_supervisor' => $supervisor,
        'field_pdf' => $fid,
        'field_state' => 'approved_supervisor',
        'field_reason' => $reason,
        'field_duties' => $duties,
        'field_application_type' => 'application'
      ]; 

    } 
    else {
      $fields = [
        'field_iteration' => $nid,
        'field_group' => $gid,
        'field_supervisor' => $supervisor,
        'field_reason' => $reason,
        'field_duties' => $duties,
        'field_application_type' => 'application'
      ]; 
    }

    
    $application = Application::create($fields);
    $application->setOwnerId($uid);
    $application->save();
    
  

    // On success leave a message and reload the page.
    \Drupal::messenger()->addStatus(t('You have successfully applied!'));
    return $response->addCommand(new CloseDialogCommand());
  }

  /**
   * Handles switching the available regions based on the selected theme.
   */
  public function ajaxShowFileUploadCallback($form, FormStateInterface $form_state) {
    return $form['replace_file_upload_container'];
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
   * Get group object where event enrollment is posted in.
   *
   * Returns the id group id for the group content.
   *
   * @return integer
   *   The group id from group content.
   */
  public function getGroupId($node) {
    $groupcontents = GroupContent::loadByEntity($node);

    $group_id = FALSE;
    // Only react if it is actually posted inside a group.
    if (!empty($groupcontents)) {
      foreach ($groupcontents as $groupcontent) {
        /** @var \Drupal\group\Entity\GroupContent $groupcontent */
        $group_id = $groupcontent->getGroup()->id();
        /** @var \Drupal\group\Entity\Group $group */
      }
    } 

    return $group_id;
  
  }

  public function getSupervisorName($supervisor) {

    $profiles = \Drupal::entityTypeManager()
  	->getStorage('profile')
  	->loadByProperties([
    	'uid' => $supervisor,
    	'type' => 'profile',
  	]);

    foreach ($profiles as $profile) {      
      return $profile->field_profile_first_name->value . ' ' . $profile->field_profile_last_name->value;       
    }

    return false;

  }




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
