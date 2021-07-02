<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Iteration Welcome Message entity.
 *
 * @ConfigEntityType(
 *   id = "iteration_welcome_message",
 *   label = @Translation("Iteration Welcome Message"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\social_moodle_iteration_enrollment_welcome_message\IterationWelcomeMessageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\social_moodle_iteration_enrollment_welcome_message\Form\IterationWelcomeMessageForm",
 *       "edit" = "Drupal\social_moodle_iteration_enrollment_welcome_message\Form\IterationWelcomeMessageForm",
 *       "delete" = "Drupal\social_moodle_iteration_enrollment_welcome_message\Form\IterationWelcomeMessageDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\social_moodle_iteration_enrollment_welcome_message\IterationWelcomeMessageHtmlRouteProvider",
 *     },
 * "access" = "Drupal\social_moodle_iteration_enrollment_welcome_message\IterationWelcomeMessageAccessControlHandler",
 *   },
 *   config_prefix = "iteration_welcome_message",
 *   admin_permission = "manage iteration welcome messages",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/iteration_welcome_message/{iteration_welcome_message}",
 *     "add-form" = "/node/{node}/iteration_welcome_message/add",
 *     "edit-form" = "/admin/iteration_welcome_message/{iteration_welcome_message}/edit",
 *     "delete-form" = "/admin/iteration_welcome_message/{iteration_welcome_message}/delete",
 *   }
 * )
 */
class IterationWelcomeMessage extends ConfigEntityBase implements IterationWelcomeMessageInterface {

  /**
   * The Iteration Welcome Message ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Iteration Welcome Message label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Iteration Welcome Message subject.
   *
   * @var string
   */
  protected $subject;

  /**
   * The Iteration Message body.
   *
   * @var array
   */
  protected $body;

    /**
   * The Iteration Message body existing.
   *
   * @var array
   */
  protected $bodyExisting;

  /**
   * The Iteration Welcome Message node.
   *
   * @var string
   */
  protected $node;


  /**
   * {@inheritdoc}
   */
  public function getSubject() {
    return $this->subject;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubject(string $subject) {
    $this->subject = $subject;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function setBody(array $body) {
    $this->body = $body;
    return $this;
  }

    /**
   * {@inheritdoc}
   */
  public function getBodyExisting() {
    return $this->body_existing;
  }

  /**
   * {@inheritdoc}
   */
  public function setBodyExisting(array $body) {
    $this->body_existing = $body;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNode() {
    return $this->node;
  }

  /**
   * {@inheritdoc}
   */
  public function setNode(string $node) {
    $this->node = $node;
    return $this;
  }

}
