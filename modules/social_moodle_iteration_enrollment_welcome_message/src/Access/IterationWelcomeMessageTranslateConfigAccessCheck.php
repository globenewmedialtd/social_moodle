<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Determines access to for translating social moodle iteration enrollment welcome messages.
 */
class IterationWelcomeMessageTranslateConfigAccessCheck implements AccessInterface {

  /**
   * Checks access to the social moodle iteration enrollment welcome message translate routes.
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, "translate iteration welcome messages");
  }

}
