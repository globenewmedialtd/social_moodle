<?php

namespace Drupal\social_moodle_enrollment;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Iteration enrollment entities.
 *
 * @ingroup social_moodle_enrollment
 */
interface IterationEnrollmentInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Iteration enrollment method where users can directly enroll.
   */
  const ENROLL_METHOD_JOIN = 1;

  /**
   * Iteration enrollment method where users need to request enrollment.
   */
  const ENROLL_METHOD_REQUEST = 2;

  /**
   * Iteration enrollment method where users need to get invited.
   */
  const ENROLL_METHOD_INVITE = 3;

  /**
   * Request created and waiting for event owners or managers response.
   */
  const REQUEST_PENDING = 0;

  /**
   * Request approved by event owner or manager.
   */
  const REQUEST_APPROVED = 1;

  /**
   * Request or invite declined by event owner, manager or user.
   */
  const REQUEST_OR_INVITE_DECLINED = 2;

  /**
   * Invited, a status to check if a user has been invited.
   */
  const INVITE_INVITED = 3;

  /**
   * Invite is pending by invited user.
   */
  const INVITE_PENDING_REPLY = 4;

  /**
   * Invite has been accepted and the user joined.
   */
  const INVITE_ACCEPTED_AND_JOINED = 5;

  /**
   * Invite is invalid or has been expired.
   */
  const INVITE_INVALID_OR_EXPIRED = 6;

  /**
   * Gets the Iteration enrollment name.
   *
   * @return string
   *   Name of the Iteration enrollment.
   */
  public function getName();

  /**
   * Sets the Iteration enrollment name.
   *
   * @param string $name
   *   The Iteration enrollment name.
   *
   * @return \Drupal\social_moodle_enrollment\IterationEnrollmentInterface
   *   The called Iteration enrollment entity.
   */
  public function setName($name);

  /**
   * Gets the Iteration enrollment creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Iteration enrollment.
   */
  public function getCreatedTime();

  /**
   * Sets the Iteration enrollment creation timestamp.
   *
   * @param int $timestamp
   *   The Iteration enrollment creation timestamp.
   *
   * @return \Drupal\social_moodle_enrollment\IterationEnrollmentInterface
   *   The called Iteration enrollment entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Iteration enrollment published status indicator.
   *
   * Unpublished Iteration enrollment are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Iteration enrollment is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Iteration enrollment.
   *
   * @param bool $published
   *   TRUE to set this Iteration enrollment to published, FALSE for unpublished.
   *
   * @return \Drupal\social_moodle_enrollment\IterationEnrollmentInterface
   *   The called Iteration enrollment entity.
   */
  public function setPublished($published);

}
