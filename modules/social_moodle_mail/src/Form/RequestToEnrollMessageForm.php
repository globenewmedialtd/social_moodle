<?php

namespace Drupal\social_moodle_mail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupType;

/**
 * Class RequestToEnrollMessageForm.
 */
class RequestToEnrollMessageForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'request_to_enroll_message_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $request_to_enroll = $this->configFactory->getEditable('social_moodle_mail.request_enroll');

    // Add an introduction text to explain what can be done here.
    $form['introduction']['warning'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Manage your request to enroll messages.'),
    ];

    $form['approve_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Message for Approve'),
      '#open' => TRUE,
    ];

    $form['approve_section']['subject_approve'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $request_to_enroll->get('subject_approve'),
      '#required' => TRUE,
    ];

    $form['approve_section']['message_approve'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Message'),
      '#default_value' => $request_to_enroll->get('message_approve')['value'],
      '#required' => TRUE,
    ];

    $form['decline_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Message for Decline'),
      '#open' => TRUE,
    ];

    $form['decline_section']['subject_decline'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $request_to_enroll->get('subject_decline'),
      '#required' => TRUE,
    ];

    $form['decline_section']['message_decline'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Message'),
      '#default_value' => $request_to_enroll->get('message_decline')['value'],
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#button_level' => 'raised',
      '#value' => $this->t('Save configuration'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('social_moodle_mail.request_enroll');
    $config->set('message_approve', $form_state->getValue('message_approve'));
    $config->set('subject_approve', $form_state->getValue('subject_approve'));
    $config->set('message_decline', $form_state->getValue('message_decline'));
    $config->set('subject_decline', $form_state->getValue('subject_decline'));    
    $config->save();
  }

  /**
   * Gets the configuration names that will be editable.
   */
  protected function getEditableConfigNames() {
    // @todo Implement getEditableConfigNames() method.
  }

}
