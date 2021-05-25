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
      $field_internal_access = $group->hasField('field_internal_access');
      if ($field_internal_access) {
        if (isset($group->field_internal_access)) {
          $enabled = $group->field_internal_access->value;
          if ($enabled === '1') {
            $is_member = $group->getMember($account);
            if (!$is_member && ($user->hasRole('internal'))) {
              return AccessResult::allowed();                            
            }
            elseif ($user->hasRole('administrator'))  {
              return AccessResult::allowed();  
            }            
          }
        }        
      }
    }

    return AccessResult::neutral();    
    
  }

}
