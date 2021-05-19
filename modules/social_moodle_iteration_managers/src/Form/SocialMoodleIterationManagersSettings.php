<?php

namespace Drupal\social_moodle_iteration_managers\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SocialMoodleIterationManagersSettings.
 *
 * @package Drupal\social_moodle_iteration_managers\Form
 */
class SocialMoodleIterationManagersSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'social_moodle_iteration_managers.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_moodle_iteration_managers_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('social_moodle_iteration_managers.settings');

    $form['author_as_manager'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Author as iteration organiser'),
      '#description' => $this->t('Set author of iteration as iteration organiser automatically.'),
      '#default_value' => $config->get('author_as_manager'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('social_moodle_iteration_managers.settings')
      ->set('author_as_manager', $form_state->getValue('author_as_manager'))
      ->save();
  }

}
