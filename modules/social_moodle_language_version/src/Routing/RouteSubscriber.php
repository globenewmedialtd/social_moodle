<?php

namespace Drupal\social_moodle_language_version\Routing;

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
    if ($route = $collection->get('view.manage_language_versions.page_group_language_versions')) {
      $route->setRequirement('_social_moodle_language_version_custom_access', 'Drupal\social_moodle_language_version\Access\SocialMoodleLanguageVersionAccessCheck');
    }
  }
}
