<?php

namespace Drupal\social_moodle_iteration_managers\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views_bulk_operations\Form\ConfigureAction;

/**
 * Action configuration form.
 */
class SocialMoodleIterationManagersManagementViewsBulkOperationsConfigureAction extends ConfigureAction {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $view_id = 'iteration_manage_enrollments', $display_id = 'page_manage_enrollments') {
    return parent::buildForm($form, $form_state, 'iteration_manage_enrollments', 'page_manage_enrollments');
  }

}
