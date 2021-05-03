<?php

namespace Drupal\social_moodle_application;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Group\Entity\Group;
use Drupal\Group\Entity\GroupInterface;

/**
 * Defines the access control handler for the application entity type.
 */
class ApplicationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    $is_group_owner = FALSE;
    $is_supervisor = FALSE;
    $is_owner = $entity->getOwnerId() === $account->id();

    // Supervisor Permission logic
    if (isset($entity->field_supervisor) && isset($entity->field_field_supervisor->entity)) {
      $supervisor_user_id = $entity->field_supervisor->entity->id();    
      if (isset($supervisor_user_id)) {
        $is_supervisor = $supervisor_user_id === $account->id();
      }
    }
   

    if ($entity->hasField('field_group')) {
      if (isset($entity->field_group) && isset($entity->field_group->entity)) {
        $group_id = $entity->field_group->entity->id();
        if (isset($group_id)) {
          $group = Group::load($group_id);
          if ($member = $group->getMember($account)) {
            if($member->hasPermission('edit group', $account)) {
              $is_group_owner = TRUE;
            }
          }
        }
      }
    }




    switch ($operation) {
      case 'view':

        if ($is_owner) {
          return AccessResult::allowedIfHasPermission($account, "view own application");
        }

        if ($is_supervisor) {
          return AccessResult::allowedIfHasPermission($account, "view supervisor application");
        }

        if ($is_group_owner) {
          return AccessResult::allowedIfHasPermission($account, "view own group application");
        }

        return AccessResult::allowedIfHasPermission($account, 'view application');

      case 'update':

        if ($is_owner) {
          return AccessResult::allowedIfHasPermission($account, "edit own application");
        }

        if ($is_supervisor) {
          return AccessResult::allowedIfHasPermission($account, "edit supervisor application");
        }

        if ($is_group_owner) {
          return AccessResult::allowedIfHasPermission($account, "edit own group application");
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
