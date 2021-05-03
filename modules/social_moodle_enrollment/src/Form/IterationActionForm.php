<?php

namespace Drupal\social_moodle_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * ExampleForm class.
 */
class IterationActionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {

    //$node = \Drupal::routeMatch()->getParameter('node');
    //$nid = $node->id();
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    $enabled_enrollment_method_buttons = [];
    $current_user = \Drupal::currentUser();   

    // First let's check if a user has been already enrolled
    $conditions = [
      'field_account' => $current_user->id(),
      'field_iteration' => $nid,
    ];

    $enrollments = \Drupal::entityTypeManager()->getStorage('iteration_enrollment')
      ->loadByProperties($conditions);    


    if ($enrollment = array_pop($enrollments)) {
      $current_enrollment_status = $enrollment->field_enrollment_status->value;
      if ($current_enrollment_status === '1') {
        $options_course_link = [
          'query' => [
            'idnumber' => $nid
          ]
        ];
        $form['enrolled']['#markup'] = '<p>' . $this->t('Enrolled') . '</p>';   
        $form['buttons']['course_link'] = [
          '#type' => 'link',
          '#title' => $this->t('Show course'),
          '#url' => Url::fromUri('internal:/moodle/redirect.php',$options_course_link),
          '#attributes' => [
            'class' => [
              'js-form-submit',
              'form-submit',
              'btn',
              'btn-accent',
              'btn-lg',
           ]
          ]
        ];
      }
    }
    
    $available_enrollment_method_buttons = $this->getAvailableButtons($nid);

    // Construct active iteration enrollment methods
    if (isset($node->field_iteration_enrollment)) {
      $enrollment_methods = $node->field_iteration_enrollment->referencedEntities();
      if (isset($enrollment_methods)) {
        foreach ($enrollment_methods as $method) {
          if (array_key_exists($method->id,$available_enrollment_method_buttons)) {
            $enabled_enrollment_method_buttons[$method->id] = $available_enrollment_method_buttons[$method->id];
          }
        }
      }
    }
  
    foreach ($enabled_enrollment_method_buttons as $key => $value) {
      $form['buttons'][$key] = $value;
    }   
  
    return $form;   

  }

  protected function getAvailableButtons($nid) {

    // Define the attributes for open to enroll
    $attributes_open_to_enroll = [
      'class' => [
        'js-form-submit',
        'form-submit',
        'btn',
        'btn-accent',
        'btn-lg',
      ]
    ];

    $open_to_enroll_button_label = t('Open to enroll');

    // Define the arributes for self application
    $attributes_self_application = [
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
        'title' => t('Request'),
        'width' => 'auto',
      ]),
    ];

    // Define the label for self application
    $self_application_button_label = t('Apply');

    // Define the arributes for self application
    $attributes_nomination_by_supervisor = [
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
        'title' => t('Request'),
        'width' => 'auto',
      ]),
    ];

    // Get the current user
    $current_user = \Drupal::currentUser();
    $supervisor = User::load($current_user->id())->hasRole('supervisor');

    // For self-applications we need to check if already applied
    $conditions = [
      'uid' => $current_user->id(),
      'field_iteration' => $nid,
    ];
    
    $application = \Drupal::entityTypeManager()->getStorage('application')
                    ->loadByProperties($conditions);

    if ($application = array_pop($application)) {
      $self_application_button_label = t('Applied');
      $self_application_add_classes = ['disabled'];
      $attributes_self_application['class'][] = 'disabled'; 
    } 

    // Define links and ensure the index has the same name
    // as the machine name of the iteration enrollment method
    $buttons = [
      'self_application' => [
        '#type' => 'link', 
        '#title' => $self_application_button_label,
        '#url' => Url::fromRoute('social_moodle_enrollment.request_application_dialog',['node' => $nid]),
        '#attributes' => $attributes_self_application
      ],
      'nomination_by_supervisor' => [
        '#type' => 'link',
        '#title' => 'Nominate',
        '#url' => Url::fromRoute('social_moodle_enrollment.request_nomination_dialog', ['node' => $nid]),
        '#attributes' => $attributes_nomination_by_supervisor
      ],
    ];

    if (!$supervisor) {
      unset($buttons['nomination_by_supervisor']);
    }

    return $buttons;

  }



  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_iteration_action_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.form_iteration_action_form'];
  }

}