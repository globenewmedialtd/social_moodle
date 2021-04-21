<?php

namespace Drupal\social_moodle_application;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the application entity type.
 */
class ApplicationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    $is_owner = $entity->getOwnerId() === $account->id();

    switch ($operation) {
      case 'view':

        if ($is_owner) {
          return AccessResult::allowedIfHasPermission($account, "view own application");
        }

        return AccessResult::allowedIfHasPermission($account, 'view application');

      case 'update':

        if ($is_owner) {
          return AccessResult::allowedIfHasPermission($account, "edit own application");
        }

        return AccessResult::allowedIfHasPermissions($account, ['edit application', 'administer application'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete application', 'administer application'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create application', 'administer application'], 'OR');
  }

}
