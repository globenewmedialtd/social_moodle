<?php

namespace Drupal\social_moodle_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\group\Entity\GroupContent;
use Drupal\social_moodle_application\Entity\Application;
use Drupal\social_moodle_application\ApplicationInterface;

/**
 * NominationForm class.
 */
class NominationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $enrollment_method = $node->get('field_iteration_enrollment')->referencedEntities();
    $nominations = [];

    $current_user = \Drupal::currentUser();
    
    $supervisor = User::load($current_user->id())->hasRole('supervisor');

    $users = social_moodle_enrollment_get_supervisor_users($current_user->id());
    


    if ($users) {
      foreach ($users as $user) {
        if ($nomination = $this->getNominee($user, $current_user->id())) {
          $nominations[] = $nomination;
        }   
      }
    }    

    $entity = \Drupal::entityTypeManager()->getStorage('application');
    $query = $entity->getQuery();
    
    $ids = $query
            ->condition('field_iteration.entity', $nid)
            ->condition('field_supervisor.entity', $current_user->id())->execute();

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
              'title' => t('Request to nomination'),
              'width' => 'auto',
            ]),
          ];



if (!empty($ids)) {
$attributes = [
            'class' => [
              'use-ajax',
              'js-form-submit',
              'form-submit',
              'btn',
              'btn-accent',
              'btn-lg',
              'disabled'
            ],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => json_encode([
              'title' => t('Request to nomination'),
              'width' => 'auto',
            ]),
          ];
}






    
    
 
    if (isset($enrollment_method) && $supervisor) {

       foreach ($enrollment_method as $method) {
         if ($method->id() === 'nomination_by_supervisor' && isset($nominations)) {

    $form['open_modal'] = [
      '#type' => 'link',
      '#title' => $this->t('Create Nomination'),
      '#url' => Url::fromRoute('social_moodle_enrollment.request_nomination_dialog', ['node' => $nid]),
      '#attributes' => $attributes
    ];

    // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';


 }  
         
}
       }

    



    return $form;




   

  }

  public function isNominationAvailable($groups) {

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
          return [
            'user_id' => $user,
            'name' => $profile->field_profile_first_name->value . ' ' . $profile->field_profile_last_name->value 
          ];
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
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_form_nomination_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.modal_form_nomination_form'];
  }

}
