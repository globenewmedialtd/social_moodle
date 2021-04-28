<?php

namespace Drupal\social_moodle_application;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining an application entity type.
 */
interface ApplicationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the application creation timestamp.
   *
   * @return int
   *   Creation timestamp of the application.
   */
  public function getCreatedTime();

  /**
   * Sets the application creation timestamp.
   *
   * @param int $timestamp
   *   The application creation timestamp.
   *
   * @return \Drupal\social_moodle_application\ApplicationInterface
   *   The called application entity.
   */
  public function setCreatedTime($timestamp);

    /**
   * Gets the order state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The order state.
   */
  public function getState();

}
