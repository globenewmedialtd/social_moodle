<?php

namespace Drupal\social_moodle_iteration_managers\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views_bulk_operations\Form\ConfigureAction;

/**
 * Action configuration form.
 */
class SocialMoodleIterationManagersEnrollmentRequestsViewsBulkOperationsConfigureAction extends ConfigureAction {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $view_id = 'iteration_manage_enrollment_requests', $display_id = 'page_manage_enrollment_requests') {
    return parent::buildForm($form, $form_state, 'iteration_manage_enrollments', 'page_manage_enrollment_requests');
  }

}
