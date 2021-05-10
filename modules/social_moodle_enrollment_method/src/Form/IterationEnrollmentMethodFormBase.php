<?php

namespace Drupal\social_moodle_enrollment_method\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IterationEnrollmentMethodFormBase.
 */
class IterationEnrollmentMethodFormBase extends EntityForm {

  /**
   * An entity query factory for the iteration_enrollment_method entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  public function __construct(EntityStorageInterface $entity_storage) {
    $this->entityStorage = $entity_storage;
  }

  public static function create(ContainerInterface $container) {
    $form = new static($container->get('entity_type.manager')->getStorage('iteration_enrollment_method'));
    $form->setMessenger($container->get('messenger'));
    return $form;
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get anything we need from the base class.
    $form = parent::buildForm($form, $form_state);

    $iteration_enrollment_method = $this->entity;

    // Build the form.
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $iteration_enrollment_method->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $iteration_enrollment_method->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ],
      '#disabled' => !$iteration_enrollment_method->isNew(),
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $iteration_enrollment_method->description,
    ];
    $form['weight'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Weight'),
      '#default_value' => $iteration_enrollment_method->weight,
    ];

    // Return the form.
    return $form;
  }

  /**
   * Checks for an existing iteration enrollment method.
   *
   * @param string|int $entity_id
   *   The entity ID.
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($entity_id, array $element, FormStateInterface $form_state) {
    // Use the query factory to build a new iteration enrollment method entity query.
    $query = $this->entityStorage->getQuery();

    // Query the entity ID to see if its in use.
    $result = $query->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();

    // We don't need to return the ID, only if it exists or not.
    return (bool) $result;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   *
   * To set the submit button text, we need to override actions().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Get the basic actins from the base class.
    $actions = parent::actions($form, $form_state);

    // Change the submit button text.
    $actions['submit']['#value'] = $this->t('Save');

    // Return the result.
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Add code here to validate your config entity's form elements.
    // Nothing to do here.
  }

  public function save(array $form, FormStateInterface $form_state) {
    // EntityForm provides us with the entity we're working on.
    $iteration_enrollment_method = $this->getEntity();

    // Drupal already populated the form values in the entity object. Each
    // form field was saved as a public variable in the entity class. PHP
    // allows Drupal to do this even if the method is not defined ahead of
    // time.
    $status = $iteration_enrollment_method->save();

    // Grab the URL of the new entity. We'll use it in the message.
    $url = $iteration_enrollment_method->toUrl();

    // Create an edit link.
    $edit_link = Link::fromTextAndUrl($this->t('Edit'), $url)->toString();

    if ($status == SAVED_UPDATED) {
      // If we edited an existing entity...
      $this->messenger()->addMessage($this->t('Iteration Enrollment Method %label has been updated.', ['%label' => $iteration_enrollment_method->label()]));
      $this->logger('contact')->notice('Iteration Enrollment Method %label has been updated.', ['%label' => $iteration_enrollment_method->label(), 'link' => $edit_link]);
    }
    else {
      // If we created a new entity...
      $this->messenger()->addMessage($this->t('Iteration Enrollment Method %label has been added.', ['%label' => $iteration_enrollment_method->label()]));
      $this->logger('contact')->notice('Iteration Enrollment Method %label has been added.', ['%label' => $iteration_enrollment_method->label(), 'link' => $edit_link]);
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('entity.iteration_enrollment_method.list');
  }

}
