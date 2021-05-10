<?php

namespace Drupal\social_moodle_enrollment_method\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the iteration_enrollment_method entity.
 *
 * The lines below, starting with '@ConfigEntityType,' are a plugin annotation.
 * These define the entity type to the entity type manager.
 *
 * The properties in the annotation are as follows:
 *  - id: The machine name of the entity type.
 *  - label: The human-readable label of the entity type. We pass this through
 *    the "@Translation" wrapper so that the multilingual system may
 *    translate it in the user interface.
 *  - handlers: An array of entity handler classes, keyed by handler type.
 *    - access: The class that is used for access checks.
 *    - list_builder: The class that provides listings of the entity.
 *    - form: An array of entity form classes keyed by their operation.
 *  - entity_keys: Specifies the class properties in which unique keys are
 *    stored for this entity type. Unique keys are properties which you know
 *    will be unique, and which the entity manager can use as unique in database
 *    queries.
 *  - links: entity URL definitions. These are mostly used for Field UI.
 *    Arbitrary keys can set here. For example, User sets cancel-form, while
 *    Node uses delete-form.
 *
 *
 * @ConfigEntityType(
 *   id = "iteration_enrollment_method",
 *   label = @Translation("Iteration Enrollment Method"),
 *   admin_permission = "administer iteration enrollment method",
 *   handlers = {
 *     "access" = "Drupal\social_moodle_enrollment_method\IterationEnrollmentMethodAccessController",
 *     "list_builder" = "Drupal\social_moodle_enrollment_method\Controller\IterationEnrollmentMethodListBuilder",
 *     "form" = {
 *       "add" = "Drupal\social_moodle_enrollment_method\Form\IterationEnrollmentMethodAddForm",
 *       "edit" = "Drupal\social_moodle_enrollment_method\Form\IterationEnrollmentMethodEditForm",
 *       "delete" = "Drupal\social_moodle_enrollment_method\Form\IterationEnrollmentMethodDeleteForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/social_moodle/social_moodle_enrollment_method/manage/{iteration_enrollment_method}",
 *     "delete-form" = "/admin/config/social_moodle/social_moodle_enrollment_method/manage/{iteration_enrollment_method}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "description",
 *     "weight"
 *   }
 * )
 */
class IterationEnrollmentMethod extends ConfigEntityBase {

  /**
   * The iteration enrollment method ID.
   *
   * @var string
   */
  public $id;

  /**
   * The iteration enrollment method UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The iteration enrollment method label.
   *
   * @var string
   */
  public $label;

  /**
   * The iteration enrollment method description.
   *
   * @var string
   */
  public $description;

  /**
   * The iteration enrollment method weight.
   *
   * @var int
   */
  public $weight;

}
