<?php

namespace Drupal\social_moodle\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Language version entities.
 *
 * @ingroup social_moodle
 */
interface LanguageVersionInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Language version name.
   *
   * @return string
   *   Name of the Language version.
   */
  public function getName();

  /**
   * Sets the Language version name.
   *
   * @param string $name
   *   The Language version name.
   *
   * @return \Drupal\social_moodle\Entity\LanguageVersionInterface
   *   The called Language version entity.
   */
  public function setName($name);

  /**
   * Gets the Language version creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Language version.
   */
  public function getCreatedTime();

  /**
   * Sets the Language version creation timestamp.
   *
   * @param int $timestamp
   *   The Language version creation timestamp.
   *
   * @return \Drupal\social_moodle\Entity\LanguageVersionInterface
   *   The called Language version entity.
   */
  public function setCreatedTime($timestamp);

}
