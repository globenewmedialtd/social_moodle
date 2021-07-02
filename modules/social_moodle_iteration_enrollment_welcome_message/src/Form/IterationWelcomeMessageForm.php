<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\GroupStorageInterface;
use Drupal\Core\Routing;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;

/**
 * Class IterationWelcomeMessageForm.
 */
class IterationWelcomeMessageForm extends EntityForm {



  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $iteration_welcome_message = $this->entity;

    // Make the label node id to avoid dupication
    // Set the entity reference field and attach given node_id

    // Get the node id
    $node_id = \Drupal::routeMatch()->getParameter('node');

    // Get settings
    $settings = $this->config('social_moodle_iteration_enrollment_welcome_message.settings');


    if ($this->operation == 'add') {

      $iteration_welcome_message->setNode($node_id);
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($iteration_welcome_message->getNode());

      $label_default_value = $this->t('Welcome Message for') . ' ' .
                             $node->id() . '-' . $node->label();
      $iteration_welcome_message->set('label', $label_default_value);

    }

    // Change page title for the edit operation
    if ($this->operation == 'edit') {
      // Get the node id
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($iteration_welcome_message->getNode());

    }

    $form['#attached']['library'][] = 'social_moodle_iteration_enrollment_welcome_message/design';


    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $iteration_welcome_message->label(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['hidden']],
      '#disabled' => TRUE,
      '#title_display' => 'invisible'
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $iteration_welcome_message->id(),
      '#machine_name' => [
        'exists' => '\Drupal\social_moodle_iteration_enrollment_welcome_message\Entity\IterationWelcomeMessage::load',
      ],
      // Hide the machine name
      '#disabled' => !$iteration_welcome_message->isNew(),
    ];


    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#maxlength' => 255,
      '#default_value' => $iteration_welcome_message->getSubject(),
      '#required' => TRUE,
    ];

 
    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => $iteration_welcome_message->getBody()['value'],
      '#required' => TRUE,
      '#format' => $settings->get('selected_format'),
      '#allowed_formats' => [
        $settings->get('selected_format')
      ]
    ];

    $form['body_existing'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body existing'),
      '#default_value' => $iteration_welcome_message->getBodyExisting()['value'],
      '#required' => FALSE,
      '#format' => $settings->get('selected_format'),
      '#allowed_formats' => [
        $settings->get('selected_format')
      ]
    ];

    $form['node'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => $node,
      '#title' => $this->t('Iteration'),
      '#disabled' => TRUE
    ];

    $form['available_tokens'] = array(
      '#type' => 'details',
      '#title' => t('Available Tokens'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );

    $suppported_tokens = array('site','user','group','node');
    
    $available_field_tokens = \Drupal::service('social_moodle_iteration_enrollment_welcome_message.available_fields');
    $whitelist = $available_field_tokens->getAvailableFields();

    $options = [
      'show_restricted' => TRUE,
      'show_nested' => TRUE,
      'global_types' => FALSE,
      'whitelist' => $whitelist
    ];  

    $form['available_tokens']['#access'] = $settings->get('show_token_info');

    $form['available_tokens']['tokens'] = \Drupal::service('social_moodle_iteration_enrollment_welcome_message.tree_builder')
      ->buildRenderable($suppported_tokens,$options);



    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get Allowed Tokens.
    $available_field_tokens = \Drupal::service('social_moodle_iteration_enrollment_welcome_message.available_fields');
    $whitelist = $available_field_tokens->getAvailableFields();
    // Validate Subject
    $tokens_present = preg_match_all("#\[(.*?)\]#", $form_state->getValue('subject'), $matches);
    if ($tokens_present) {

      $found_tokens = $matches[0];
      $wrong_tokens = array_diff($found_tokens,$whitelist);

      if (count($wrong_tokens) > 0) {
        $form_state->setErrorByName('subject', $this->t('Illegal Tokens found in subject.'));
      }     

    }

    // Validate Body
    $tokens_present = preg_match_all("#\[(.*?)\]#", $form_state->getValue('body')['value'], $matches);
    if ($tokens_present) {
    
      $found_tokens = $matches[0];
      $wrong_tokens = array_diff($found_tokens,$whitelist);
    
      if (count($wrong_tokens) > 0) {
        $form_state->setErrorByName('body', $this->t('Illegal Tokens found in body.'));
      }     
    
    }

    // Validate Body
    $tokens_present = preg_match_all("#\[(.*?)\]#", $form_state->getValue('body_existing')['value'], $matches);
    if ($tokens_present) {
        
      $found_tokens = $matches[0];
      $wrong_tokens = array_diff($found_tokens,$whitelist);
        
      if (count($wrong_tokens) > 0) {
        $form_state->setErrorByName('body_existing', $this->t('Illegal Tokens found in body.'));
      }     
        
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $iteration_welcome_message = $this->entity;

    $status = $iteration_welcome_message->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Welcome Message.', [
          '%label' => $iteration_welcome_message->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Welcome Message.', [
          '%label' => $iteration_welcome_message->label(),
        ]));
    }

    // Prepare redirect route
    $redirect_route_name = 'view.iteration_manage_enrollments.page_manage_enrollments';


    if ($status != SAVED_NEW) {  

      $url = Url::fromRoute($redirect_route_name,['node' => $iteration_welcome_message->getNode()]);
      $form_state->setRedirectUrl($url);

    }




  }

}
