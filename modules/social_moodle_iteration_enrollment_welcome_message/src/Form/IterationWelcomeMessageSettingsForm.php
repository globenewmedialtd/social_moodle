<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Iteration Welcome Message settings.
 */
class IterationWelcomeMessageSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iteration_welcome_message_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'social_moodle_iteration_enrollment_welcome_message.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $settings = $this->config('social_moodle_iteration_enrollment_welcome_message.settings');

    $available_filters = filter_formats();

    foreach($available_filters as $id => $filter) {
      $filter_options[$id] = $filter->label();
    }  

    $form['selected_format'] = [
      '#type'          => 'select',
      '#options'       => $filter_options,
      '#default_value' => $settings->get('selected_format'),
    ];

    $form['show_token_info'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Show token info'),
      '#default_value' => $settings->get('show_token_info'),
      '#description' => $this->t('If enabled a bok with available tokens are shown to users.')
    ];
    

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->configFactory->getEditable('social_moodle_iteration_enrollment_welcome_message.settings');

    // Save configurations.
    $settings->set('selected_format', $form_state->getValue('selected_format'))->save();
    $settings->set('show_token_info', $form_state->getValue('show_token_info'))->save();

  }

}

