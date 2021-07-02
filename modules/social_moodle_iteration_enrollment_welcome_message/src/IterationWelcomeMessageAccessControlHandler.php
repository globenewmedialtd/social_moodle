<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeInterface;
use Drupal\user\Entity\User;


/**
 * Access controller for the iteration_welcome_message Entity.
 *
 * @see \Drupal\social_moodle_iteration_enrollment_welcome_message\Entity\IterationWelcomeMessage.
 */
class IterationWelcomeMessageAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    // Load the user for Role check
    $user = User::load($account->id());

    switch ($operation) {
      case 'view':
       // Here we can access the getNode()
       $nid = $entity->getNode();
       $groupHelperService = \Drupal::service('social_group.helper_service');
       $entityTypeManager = \Drupal::service('entity_type.manager');

      // We need to get the group via groupHelperService
      $gid_from_entity = $groupHelperService->getGroupFromEntity([
        'target_type' => 'node',
        'target_id' => $nid,
      ]);

 
      if ($gid_from_entity !== NULL) {
        /** @var \Drupal\group\Entity\GroupInterface $group */
        $group = $entityTypeManager
          ->getStorage('group')
          ->load($gid_from_entity);
      }

      if ($group) {

          $member = $group->getMember($account);

          if ($member) {
            if($member->hasPermission('edit group', $account)) {
              return AccessResult::allowedIfHasPermission($account, 'manage iteration welcome messages');
            }
          }
          elseif ($user->hasRole('administrator')) {
            return AccessResult::allowedIfHasPermission($account, 'manage iteration welcome messages')->cachePerUser();
          }

        }

        
      case 'update':    

        $user = User::load($account->id());
	// Here we can access the getNode()
       $nid = $entity->getNode();
        $groupHelperService = \Drupal::service('social_group.helper_service');
       $entityTypeManager = \Drupal::service('entity_type.manager');

      // We need to get the group via groupHelperService
      $gid_from_entity = $groupHelperService->getGroupFromEntity([
        'target_type' => 'node',
        'target_id' => $nid,
      ]);

 
      if ($gid_from_entity !== NULL) {
        /** @var \Drupal\group\Entity\GroupInterface $group */
        $group = $entityTypeManager
          ->getStorage('group')
          ->load($gid_from_entity);
      }

        if ($group) {

          $member = $group->getMember($account);

          if ($member) {
            if($member->hasPermission('edit group', $account)) {
              return AccessResult::allowedIfHasPermission($account, 'manage iteration welcome messages');
            }
          }
          elseif ($user->hasRole('administrator')) {
            return AccessResult::allowedIfHasPermission($account, 'manage iteration welcome messages')->cachePerUser();
          }

        }

        return AccessResult::forbidden();

      case 'delete':

        // Users with 'cancel account' permission can cancel their own account.
        //return AccessResult::allowedIf($account_client == $entity_client)
          //->cachePerUser();
    }

    // No opinion.
    return AccessResult::neutral();


  }


  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {

    // Load the user for Role check
    $user = User::load($account->id());

    $nid = \Drupal::routeMatch()->getRawParameter('node');
       $groupHelperService = \Drupal::service('social_group.helper_service');
       $entityTypeManager = \Drupal::service('entity_type.manager');

      // We need to get the group via groupHelperService
      $gid_from_entity = $groupHelperService->getGroupFromEntity([
        'target_type' => 'node',
        'target_id' => $nid,
      ]);

 
      if ($gid_from_entity !== NULL) {
        /** @var \Drupal\group\Entity\GroupInterface $group */
        $group = $entityTypeManager
          ->getStorage('group')
          ->load($gid_from_entity);
      }
   

    if ($group) {

      $member = $group->getMember($account);

      if ($member) {
        if($member->hasPermission('edit group', $account)) {
          return AccessResult::allowedIfHasPermission($account, 'manage iteration welcome messages');
        }
      }
      elseif ($user->hasRole('administrator')) {
        return AccessResult::allowedIfHasPermission($account, 'manage iteration welcome messages');
      }

    }

    return AccessResult::forbidden();

  }
    

}
