<?php

namespace Drupal\social_moodle_internal_group_visibility\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('social_group.stream')) {
      $route->setRequirement('_social_moodle_internal_custom_access', 'Drupal\social_moodle_internal_group_visibility\Access\SocialMoodleInternalAccessCheck');
    }
    if ($route = $collection->get('view.group_information.page_group_about')) {
      $route->setRequirement('_social_moodle_internal_custom_access', 'Drupal\social_moodle_internal_group_visibility\Access\SocialMoodleInternalAccessCheck');
    }
    if ($route = $collection->get('view.group_events.page_group_events')) {
      $route->setRequirement('_social_moodle_internal_custom_access', 'Drupal\social_moodle_internal_group_visibility\Access\SocialMoodleInternalAccessCheck');
    }
    if ($route = $collection->get('view.group_topics.page_group_topics')) {
      $route->setRequirement('_social_moodle_internal_custom_access', 'Drupal\social_moodle_internal_group_visibility\Access\SocialMoodleInternalAccessCheck');
    }   
    if ($route = $collection->get('view.group_members.page_group_members')) {
      $route->setRequirement('_social_moodle_internal_custom_access', 'Drupal\social_moodle_internal_group_visibility\Access\SocialMoodleInternalAccessCheck');
    }
  }
}
