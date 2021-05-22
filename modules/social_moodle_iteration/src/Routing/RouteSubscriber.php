<?php

namespace Drupal\social_moodle_iteration\Routing;

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
    if ($route = $collection->get('view.group_iterations.page_group_iterations')) {
      $route->setRequirement('_social_moodle_iteration_custom_access', 'Drupal\social_moodle_iteration\Access\SocialMoodleIterationAccessCheck');
    }
  }
}
