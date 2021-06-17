<?php

namespace Drupal\social_moodle_iteration;

use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Session\AccountProxy;

/**
 * Interface SocialMoodleIterationEnrollmentInfoInterface.
 *
 * @package Drupal\social_moodle_iteration
 */
interface SocialMoodleIterationEnrollmentInfoInterface {

  public function getDefaultIterationRecords(GroupInterface $group);

  public function getEnrolledIterationRecords(GroupInterface $group);

  public function getPendingIterationRecords(GroupInterface $group);

}
