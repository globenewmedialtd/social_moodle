<?php

namespace Drupal\social_moodle_application\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the application entity edit forms.
 */
class ApplicationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New application %label has been created.', $message_arguments));
      $this->logger('social_moodle_application')->notice('Created new application %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The application %label has been updated.', $message_arguments));
      $this->logger('social_moodle_application')->notice('Updated new application %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.application.canonical', ['application' => $entity->id()]);
  }

}
