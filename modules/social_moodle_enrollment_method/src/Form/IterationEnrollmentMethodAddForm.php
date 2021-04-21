<?php

namespace Drupal\social_moodle_enrollment_method\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class IterationEnrollmentMethodAddForm.
 *
 * Provides the add form for our IterationEnrollmentMethod entity.
 *
 * @ingroup social_moodle_enrollment_method
 */
class IterationEnrollmentMethodAddForm extends IterationEnrollmentMethodFormBase {

  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Create Iteration Enrollment Method');
    return $actions;
  }

}
