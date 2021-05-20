<?php

namespace Drupal\social_moodle_iteration_invite\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\social_moodle_iteration_invite\Routing
 * Listens to the dynamic route iterations.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('social_moodle_iteration_invite.invite_email')) {
      $requirements = $route->getRequirements();
      $requirements['_custom_access'] = 'social_moodle_iteration_invite.access::iterationFeatureAccess';
      $route->setRequirements($requirements);
    }

    if ($route = $collection->get('social_moodle_iteration_invite.invite_user')) {
      $requirements = $route->getRequirements();
      $requirements['_custom_access'] = 'social_moodle_iteration_invite.access::iterationFeatureAccess';
      $route->setRequirements($requirements);
    }

    if ($route = $collection->get('view.iteration_manage_enrollment_invites.page_manage_enrollment_invites')) {
      $requirements = $route->getRequirements();
      $requirements['_custom_access'] = 'social_moodle_iteration_invite.access::iterationFeatureAccess';
      $route->setRequirements($requirements);
    }

    if ($route = $collection->get('view.user_iteration_invites.page_user_iteration_invites')) {
      $requirements = $route->getRequirements();
      $requirements['_custom_access'] = 'social_moodle_iteration_invite.access::userInviteAccess';
      $route->setRequirements($requirements);
    }
  }

}
