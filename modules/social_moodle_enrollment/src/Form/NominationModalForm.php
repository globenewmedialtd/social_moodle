<?php

namespace Drupal\social_moodle_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\user\Entity\User;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;

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

    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $enrollment_method = $node->get('field_iteration_enrollment')->referencedEntities();

    $current_user = \Drupal::currentUser();
    
    $supervisor = User::load($current_user->id())->hasRole('supervisor');

    $groups = $this->getGroups($node);

    foreach ($groups as $group) {
    	$members = $group->getMembers();
      foreach ($members as $member) {
        $users[$member->getUser()->id()] = $member->getUser()->id();
      }
    }

   foreach ($users as $user) {

     if ($nomination = $this->getNominee($user, $current_user->id())) {
       $nominations[$user] = $nomination;
     }
  
   }
    
    
    $form['#prefix'] = '<div id="modal_nomination_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    

    $form['nominate_users'] = array(
      '#type' => 'checkboxes',
      '#options' => $nominations,
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
