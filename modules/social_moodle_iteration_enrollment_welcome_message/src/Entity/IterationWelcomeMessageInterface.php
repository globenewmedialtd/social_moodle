<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Iteration Welcome Message entities.
 */
interface IterationWelcomeMessageInterface extends ConfigEntityInterface {

  // Add get/set methods for your configuration properties here.
  public function getSubject();

  public function setSubject(string $subject);

  public function getBody();

  public function setBody(array $body);

  public function getBodyExisting();

  public function setBodyExisting(array $body);

  public function getNode();

  public function setNode(string $node);


}
