<?php

namespace Drupal\social_moodle_enrollment_method\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class IterationEnrollmentMethodDeleteForm.
 *
 */
class IterationEnrollmentMethodDeleteForm extends EntityConfirmFormBase {

  public function getQuestion() {
    return $this->t('Are you sure you want to delete Iteration Enrollment Method %label?', [
      '%label' => $this->entity->label(),
    ]);
  }

  public function getConfirmText() {
    return $this->t('Delete Iteration Enrollment Method');
  }

  public function getCancelUrl() {
    return new Url('entity.iteration_enrollment_method.list');
  }

  /**
   * The submit handler for the confirm form.
   *
   * For entity delete forms, you use this to delete the entity in
   * $this->entity.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Delete the entity.
    $this->entity->delete();

    // Set a message that the entity was deleted.
    $this->messenger()->addMessage($this->t('Iteration Enrollment Method %label was deleted.', [
      '%label' => $this->entity->label(),
    ]));

    // Redirect the user to the list controller when complete.
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
