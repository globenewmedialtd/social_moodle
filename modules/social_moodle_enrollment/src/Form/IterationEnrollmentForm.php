<?php

namespace Drupal\social_moodle_enrollment\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Iteration enrollment edit forms.
 *
 * @ingroup social_moodle_enrollment
 */
class IterationEnrollmentForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\social_moodle_enrollment\Entity\IterationEnrollment $entity */
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label Iteration enrollment.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label Iteration enrollment.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.iteration_enrollment.canonical', ['iteration_enrollment' => $entity->id()]);
  }

}
