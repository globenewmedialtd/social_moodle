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
class IterationNominationRequestModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'request_iteration_nomination_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $gid = $this->getGroupId($node);
    $nominees_applied = [];    

    $current_user = \Drupal::currentUser();
    
    $supervisor = User::load($current_user->id())->hasRole('supervisor');

    $users = social_moodle_enrollment_get_supervisor_users($current_user->id());

    foreach ($users as $user) {
      if ($nomination = $this->getNominee($user, $current_user->id())) {
        $nominations[$user] = $nomination;
      }
    }

    if ($users) {
      $nominees_applied = $this->getNomineesAlreadyApplied($users,$nid);
    }     


    $form['#prefix'] = '<div id="request_iteration_nomination">';
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

    $form['group'] = [
      '#type' => 'hidden',
      '#value' => $gid,
    ];

    $form['nominate_users'] = array(
      '#type' => 'checkboxes',
      '#options' => $nominations,
      '#required' => TRUE,
    );  

    // Make already nominees disabled
    if (isset($nominees_applied) && !empty($nominees_applied)) {
      foreach ($nominees_applied as $nominee) {        
        $form['nominate_users'][$nominee]['#disabled'] = TRUE;        
      }
    }    


    // A required reason field.
    $form['reason'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reason'),
      '#required' => TRUE,
    ];

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

    $nid = $form_state->getValue('iteration');
    $gid = $form_state->getValue('group');

    $nominations = $form_state->getValue('nominate_users');
    $reason = $form_state->getValue('reason'); 

    foreach ($nominations as $key => $nominee) {
	    if ($key == $nominee) {	           
        $fields = [
          'field_iteration' => $nid,
          'field_group' => $gid,
          'field_supervisor' => $uid,
          'field_reason' => $reason,
          'field_application_type' => 'nomination'
        ];
        // Create a new application for the iteration.
        $application = Application::create($fields);
        $application->setOwnerId($nominee);
        $application->save();
      }	
    }

  

    // On success leave a message and reload the page.
    \Drupal::messenger()->addStatus(t('Your nomination has been sent successfully'));
    return $response->addCommand(new CloseDialogCommand());
  }

  public function getNominee($user, $supervisor_uid) {

    $profiles = \Drupal::entityTypeManager()
  	->getStorage('profile')
  	->loadByProperties([
    	'uid' => $user,
    	'type' => 'profile',
  	]);

    foreach ($profiles as $profile) {
      if ($profile->hasField('field_supervisor')) {
        if ($profile->field_supervisor->target_id === $supervisor_uid) {
          return $profile->field_profile_first_name->value . ' ' . $profile->field_profile_last_name->value; 
          
        }      
      }
    }

    return false;

  }

  public function getNomineesAlreadyApplied($users, $nid) {

    $applied_users = [];

    foreach($users as $user) {
      $conditions = [
        'uid' => $user,
        'field_iteration' => $nid
      ];

      $application = \Drupal::entityTypeManager()->getStorage('application')
                    ->loadByProperties($conditions);

      if (isset($application) && !empty($application)) {
        $applied_users[$user] = $user; 
      }

    }

    return $applied_users; 

  }

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
    return ['config.request_iteration_enrollment_modal_form'];
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
