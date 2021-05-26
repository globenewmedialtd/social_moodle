<?php

namespace Drupal\social_moodle_internal_group_visibility\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\Entity\User;


/**
 * Determines access for iteration management view.
 */
class SocialMoodleInternalAccessCheck implements AccessInterface {


  /**
   * Checks access to the view
   */
  public function access(Route $route, RouteMatch $route_match, AccountInterface $account) {    

    $parameters = $route_match->getParameters();
    $group = $parameters->get('group');
    $user_id = $account->id();
    $user = User::load($user_id); 
    

    if (!is_null($group) && (!$group instanceof GroupInterface)) {   
      $group = Group::load($group);
    }

    if ($group instanceof GroupInterface) {

      // We need to know If user is a member
      $is_member = $group->getMember($account);
      
      // First we need to know the group type
      $group_type = $group->bundle();  
      
      if ($group_type === 'public_group') {
        // Make sure to allow all!
        return AccessResult::allowed();
      }

      if ($group_type === 'open_group') {
        // Make sure to allow community users to access that
        if ($user->hasRole('authenticated')) {
          return AccessResult::allowed();
        }
        if ($is_member) {
          return AccessResult::allowed();
        }
      }
      
      if ($group_type === 'closed_group') {
        if ($is_member) {
          return AccessResult::allowed();
        }
      } 
      
      if ($group_type === 'secret_group') {
        if ($is_member) {
          return AccessResult::allowed();
        }
      }  

      if ($group_type === 'flexible_group' ||
          $group_type === 'tm_community' ||
          $group_type === 'tm_conference') {
        // Check existence of visiblity field
        $group_visibility = $group->hasField('field_flexible_group_visibility');
        if($group_visibility) {
          if (isset($group->field_flexible_group_visibility)) {
            $allowed_visibility = $group->field_flexible_group_visibility->value;
            if ($allowed_visibility === 'public') {
              return AccessResult::allowed();
            }
            if ($allowed_visibility === 'community') {
              if ($user->hasRole('authenticated')) {
                return AccessResult::allowed();
              } 
              if ($is_member) {
                return AccessResult::allowed();
              }             
            }
            if ($allowed_visibility === 'members') {
              if ($is_member) {
                return AccessResult::allowed();
              }              
            }
          }
        } 

        return AccessResult::forbidden();

      } 

      // We want check only here
      if ($group_type === 'tm_training') {

        // Check our special field here
        $internal_role_check = FALSE;
        $field_internal_access = $group->hasField('field_internal_access');
        if ($field_internal_access) {
          if (isset($group->field_internal_access)) {
            if($group->field_internal_access->value === '1') {
              $internal_role_check = TRUE;
            }
          }
        }

        // Check existence of visiblity field
        $group_visibility = $group->hasField('field_flexible_group_visibility');
        if($group_visibility) {
          if (isset($group->field_flexible_group_visibility)) {
            $allowed_visibility = $group->field_flexible_group_visibility->value;
            if ($allowed_visibility === 'public') {
              return AccessResult::allowed();
            }
            if ($allowed_visibility === 'community') {
              if ($internal_role_check) {
                if ($user->hasRole('internal')) {
                  return AccessResult::allowed();
                } 
              }
              else {
                if ($user->hasRole('authenticated')) {
                  return AccessResult::allowed();
                } 
              }           
              if ($is_member) {
                return AccessResult::allowed();
              }             
            }
            if ($allowed_visibility === 'members') {
              if ($is_member) {
                return AccessResult::allowed();
              }              
            }
          }
        }
      }
    }

    return AccessResult::neutral();    
    
  }

}
