<?php

namespace Drupal\social_moodle_iteration_invite\Access;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Access\AccessResult;
use Drupal\social_moodle_iteration_invite\SocialMoodleIterationInviteAccessHelper;

/**
 * Class SocialMoodleIterationInvitesAccess.
 *
 * @package Drupal\social_moodle_iteration_invite\Access
 */
class SocialMoodleIterationInvitesAccess {

  /**
   * The iteration invite access helper.
   *
   * @var \Drupal\social_moodle_iteration_invite\SocialMoodleIterationInviteAccessHelper
   */
  protected $accessHelper;

  /**
   * IterationInvitesAccess constructor.
   *
   * @param \Drupal\social_moodle_iteration_invite\SocialMoodleIterationInviteAccessHelper $accessHelper
   *   The iteration invite access helper.
   */
  public function __construct(SocialMoodleIterationInviteAccessHelper $accessHelper) {
    $this->accessHelper = $accessHelper;
  }

  /**
   * Custom access check on the invite features on iterations.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns the result of the access helper.
   *
   * @see \Drupal\social_moodle_iteration_invite\SocialMoodleIterationInviteAccessHelper::iterationFeatureAccess()
   */
  public function iterationFeatureAccess() {
    try {
      return $this->accessHelper->iterationFeatureAccess();
    }
    catch (InvalidPluginDefinitionException $e) {
      return AccessResult::neutral();
    }
    catch (PluginNotFoundException $e) {
      return AccessResult::neutral();
    }
  }

  /**
   * Custom access check for the user invite overview.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns the result of the access helper.
   *
   * @see \Drupal\social_moodle_iteration_invite\SocialMoodleIterationInviteAccessHelper::userInviteAccess()
   */
  public function userInviteAccess() {
    return $this->accessHelper->userInviteAccess();
  }

}
