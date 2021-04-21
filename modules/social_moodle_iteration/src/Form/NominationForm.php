<?php

namespace Drupal\social_moodle_iteration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

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

    $current_user = \Drupal::currentUser();
    
    $supervisor = User::load($current_user->id())->hasRole('supervisor');
    
 
    if (isset($enrollment_method) && $supervisor) {

       foreach ($enrollment_method as $method) {
         if ($method->id() === 'nomination_by_supervisor') {

    $form['open_modal'] = [
      '#type' => 'link',
      '#title' => $this->t('Create Nomination'),
      '#url' => Url::fromRoute('modal_form_nomination.open_modal_form'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'button',
	  'btn',
          'btn-accent brand-bg-accent',
          'btn-lg btn-raised',
          'waves-effect',
        ],
      ],
    ];

    // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';


 }  
         
}
       }

    



    return $form;




   

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
