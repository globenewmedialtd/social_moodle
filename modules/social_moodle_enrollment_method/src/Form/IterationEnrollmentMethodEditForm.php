<?php

namespace Drupal\social_moodle_enrollment_method\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class IterationEnrollmentMethodEditForm.
 */
class IterationEnrollmentMethodEditForm extends IterationEnrollmentMethodFormBase {

  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update Iteration Enrollment Method');
    return $actions;
  }

}
