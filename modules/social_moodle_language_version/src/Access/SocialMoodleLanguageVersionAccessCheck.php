<?php

namespace Drupal\social_moodle_language_version\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Config\ConfigFactoryInterface;


/**
 * Determines access for language version view.
 */
class SocialMoodleLanguageVersionAccessCheck implements AccessInterface {


  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Checks access to the view
   */
  public function access(Route $route, RouteMatch $route_match) {    

    $config = $this->configFactory->get('social_moodle.settings');
    $active_group_types = $config->get('social_moodle_group_types');    
    $parameters = $route_match->getParameters();
    $group = $parameters->get('group');
    $enabled_group_types = [];

    if (!is_null($group) && (!$group instanceof GroupInterface)) {
      $group = Group::load($group);
      $group_type = $group->getGroupType()->id();

      foreach($active_group_types as $id => $label) {
        if ($id === $label) {
          $enabled_group_types[$id] = $label;
        }
      }

      if(isset($enabled_group_types)){
        if(in_array($group_type,$enabled_group_types)) {
          return AccessResult::allowed();  
        }
      }

    }

    return AccessResult::forbidden();    
    
  }

}
