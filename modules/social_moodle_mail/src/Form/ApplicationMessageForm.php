<?php

namespace Drupal\social_moodle_mail\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;


/**
 * Application Message form.
 *
 * @property \Drupal\social_moodle_mail\ApplicationMessageInterface $entity
 */
class ApplicationMessageForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);
    
    $transition_disabled = TRUE;
    $application_message = $this->entity;  
    $new_application = $application_message->isNew();     

    if ($new_application) {
      $transition_disabled = FALSE;      
    }

    $transition_options = [
      'applied' => 'Applied',
      'approved_supervisor' => 'Approved by Supervisor',
      'denied_supervisor' => 'Denied by Supervisor',
      'approved_lnd' => 'Approved by LnD',
      'denied_lnd' => 'Denied by LnD',
      'waitlist' => 'Waitlist',
      'reminder' => 'Reminder'
    ];

    
   
    // Attendee
    $form['fieldset_attendee'] = [
      '#type' => 'details',
      '#title' => $this->t('Attendee'),
      '#description' => $this->t('An email will be sent when all fields have been filled out.'),
      '#open' => TRUE,  
      '#tree' => FALSE,
    ];

    // Supervisor
    $form['fieldset_supervisor'] = [
      '#type' => 'details',
      '#title' => $this->t('Supervisor'),
      '#description' => $this->t('An email will be sent when all fields have been filled out.'),
      '#open' => TRUE,
      '#tree' => FALSE,
    ];

    $form['fieldset_attendee']['subject_attendee'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#maxlength' => 255,
      '#default_value' => $application_message->getSubjectAttendee(),
      '#required' => FALSE,     
    ];
 
    $form['fieldset_attendee']['body_attendee'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => isset ($application_message->getBodyAttendee()['value']) ? $application_message->getBodyAttendee()['value'] : '',
      '#required' => FALSE,
      '#format' => 'full_html',
      '#allowed_formats' => [
        'full_html'
      ],
      '#tree' => FALSE,
    ];
    


    $form['fieldset_supervisor']['subject_supervisor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#maxlength' => 255,
      '#default_value' => $application_message->getSubjectSupervisor(),
      '#required' => FALSE,
    ];

    $form['fieldset_supervisor']['body_supervisor'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => isset ($application_message->getBodySupervisor()['value']) ? $application_message->getBodySupervisor()['value'] : '',
      '#required' => FALSE,
      '#format' => 'full_html',
      '#allowed_formats' => [
        'full_html'
      ],
    ];


    // Manager
    $form['fieldset_manager'] = [
      '#type' => 'details',
      '#title' => $this->t('Course Manager'),
      '#description' => $this->t('An email will be sent when all fields have been filled out.'),
      '#open' => FALSE,
    ];

    $form['fieldset_manager']['subject_manager'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#maxlength' => 255,
      '#default_value' => $application_message->getSubjectManager(),
      '#required' => FALSE,
      '#tree' => FALSE,
    ];

    $form['fieldset_manager']['body_manager'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => isset ($application_message->getBodyManager()['value']) ? $application_message->getBodyManager()['value'] : '',
      '#required' => FALSE,
      '#format' => 'full_html',
      '#allowed_formats' => [
        'full_html'
      ],
      '#tree' => FALSE,
    ];

    // Lnd
    $form['fieldset_lnd'] = [
      '#type' => 'details',
      '#title' => $this->t('LnD'),
      '#description' => $this->t('An email will be sent when all fields have been filled out.'),
      '#open' => FALSE,
    ];

    $form['fieldset_lnd']['subject_lnd'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#maxlength' => 255,
      '#default_value' => $application_message->getSubjectLnd(),
      '#required' => FALSE,
      '#tree' => FALSE,
    ];

    $form['fieldset_lnd']['body_lnd'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => isset ($application_message->getBodyLnd()['value']) ? $application_message->getBodyLnd()['value'] : '',
      '#required' => FALSE,
      '#format' => 'full_html',
      '#allowed_formats' => [
        'full_html'
      ],
      '#tree' => FALSE,
    ];
    
    $form['transition'] = [
      '#type' => 'select',
      '#title' => $this->t('Transition'),
      '#options' => $transition_options,
      '#default_value' => $application_message->getTransition(),
      '#description' => $this->t('Please select a transition for sending the application message.'),
      '#disabled' => $transition_disabled,
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    //kint($form_state->getValues());
    

    $application_message = $this->entity;


    //kint($application_message);
    //exit;

    $new_application = $application_message->isNew();

    if ($new_application) {
      $transition = $form_state->getValue('transition');
      $application_message_storage = \Drupal::entityTypeManager()->getStorage('application_message');
      $application_message_record = $application_message_storage->load($transition);
      if (isset($application_message_record) && !empty($application_message_record)) {
        $form_state->setErrorByName('transition', $this->t('You already have an application message for that workflow.'));
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {    
   
    $application_message = $this->entity;
    // Save the transition as entity id

    $new_application = $application_message->isNew();

    if ($new_application) {
      $application_message->set('id', $form_state->getValue('transition'));
    }

    //\Drupal::logger('social_moodle_mail')->warning('<pre><code>' . print_r($application_message, TRUE) . '</code></pre>');

    $result = $application_message->save();
    
    $message_args = ['%label' => $this->entity->getTransition()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new application message %label.', $message_args)
      : $this->t('Updated application message %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
