<?php

namespace Drupal\social_moodle_iteration_invite\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupType;

/**
 * Class IterationInviteSettingsForm.
 */
class IterationInviteSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iteration_invite_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $social_moodle_iteration_invite_config = $this->configFactory->getEditable('social_moodle_iteration_invite.settings');

    // Add an introduction text to explain what can be done here.
    $form['introduction']['warning'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Manage your invite messages.'),
    ];

    $form['invite_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $social_moodle_iteration_invite_config->get('invite_subject'),
      '#required' => TRUE,
    ];

    $form['invite_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#default_value' => $social_moodle_iteration_invite_config->get('invite_message'),
      '#required' => TRUE,
    ];

    $form['invite_helper'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Helper'),
      '#default_value' => $social_moodle_iteration_invite_config->get('invite_helper'),
      '#required' => TRUE,
      '#rows' => '2',
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
    $config = $this->configFactory->getEditable('social_moodle_iteration_invite.settings');
    //$config->set('invite_enroll', $form_state->getValue('invite_enroll'));
    $config->set('invite_message', $form_state->getValue('invite_message'));
    $config->set('invite_subject', $form_state->getValue('invite_subject'));
    $config->set('invite_helper', $form_state->getValue('invite_helper'));
    $config->save();
  }

  /**
   * Gets the configuration names that will be editable.
   */
  protected function getEditableConfigNames() {
    // @todo Implement getEditableConfigNames() method.
  }

}
