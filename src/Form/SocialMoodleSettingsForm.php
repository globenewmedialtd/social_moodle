<?php

namespace Drupal\social_moodle\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupType;

/**
 * Class SocialMoodleSettingsForm.
 */
class SocialMoodleSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_moodle_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $social_moodle_config = $this->configFactory->getEditable('social_moodle.settings');

    // Add an introduction text to explain what can be done here.
    $form['introduction']['warning'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Activate Social Moodle for one or more group types.'),
    ];

    $group_types = [];
    /** @var \Drupal\group\Entity\GroupType $group_type */
    foreach (GroupType::loadMultiple() as $group_type) {
      $group_types[$group_type->id()] = $group_type->label();
    }

    $form['social_moodle_group_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable Social Moodle per group type'),
      '#description' => $this->t('Select the group types for which you want to enable the Social Moodle feature.'),
      '#options' => $group_types,
      '#default_value' => $social_moodle_config->get('social_moodle_group_types'),
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
    $config = $this->configFactory->getEditable('social_moodle.settings');
    $config->set('social_moodle_group_types', $form_state->getValue('social_moodle_group_types'));
    $config->save();
  }

  /**
   * Gets the configuration names that will be editable.
   */
  protected function getEditableConfigNames() {
    // @todo Implement getEditableConfigNames() method.
  }

}

