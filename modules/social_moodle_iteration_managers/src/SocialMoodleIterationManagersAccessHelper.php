<?php

namespace Drupal\social_moodle_iteration_managers;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\NodeInterface;

/**
 * Helper class for checking update access on iteration managers nodes.
 */
class SocialMoodleIterationManagersAccessHelper {

  /**
   * NodeAccessCheck for given operation, node and user account.
   */
  public static function nodeAccessCheck(NodeInterface $node, $op, AccountInterface $account) {
    if ($op === 'update') {

      // Only for events.
      if ($node->getType() === 'iteration') {
        // Only continue if the user has access to view the event.
        if ($node->access('view', $account)) {
          // The owner has access.
          if ($account->id() === $node->getOwnerId()) {
            return 2;
          }

          $iteration_managers = $node->get('field_iteration_managers')->getValue();

          foreach ($iteration_managers as $iteration_manager) {
            if (isset($iteration_manager['target_id']) && $account->id() == $iteration_manager['target_id']) {
              return 2;
            }
          }

          // No hits, so we assume the user is not an iteration manager.
          return 1;
        }
      }
    }
    return 0;
  }

  /**
   * Gets the Entity access for the given node.
   */
  public static function getEntityAccessResult(NodeInterface $node, $op, AccountInterface $account) {
    $access = self::nodeAccessCheck($node, $op, $account);

    switch ($access) {
      case 2:
        return AccessResult::allowed()->cachePerPermissions()->addCacheableDependency($node);

      case 1:
        return AccessResult::forbidden();
    }

    return AccessResult::neutral();
  }

}
