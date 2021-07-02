<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

use Drupal\config_translation\ConfigMapperManagerInterface;
use Drupal\Core\Routing\RoutingEvents;


/**
 * Class RouteSubscriber.
 *
 * @package Drupal\social_moodle_iteration_enrollment_welcome_message\Routing
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    // Add controller for specific route
    if ($route = $collection->get('entity.iteration_welcome_message.add_form')) {
      $route->setDefault('_controller', '\Drupal\social_moodle_iteration_enrollment_welcome_message\Controller\SocialMoodleIterationEnrollmentWelcomeMessageController::redirectToEditForm');
    }
    if ($route = $collection->get('entity.iteration_welcome_message.canonical')) {
      $route->setDefault('_controller', '\Drupal\social_moodle_iteration_enrollment_welcome_message\Controller\SocialMoodleIterationEnrollmentWelcomeMessageController::viewIterationWelcomeMessage');
    }
    if ($route = $collection->get('entity.iteration_welcome_message.config_translation_overview')) {
       $requirements['_config_translation_custom_access'] = 'TRUE';
       $route->setRequirements($requirements);      
    }
    if ($route = $collection->get('config_translation.item.add.entity.iteration_welcome_message.edit_form')) {
      $requirements['_config_translation_custom_access'] = 'TRUE';
      $route->setRequirements($requirements);
    }
    if ($route = $collection->get('config_translation.item.edit.entity.iteration_welcome_message.edit_form')) {
      $requirements['_config_translation_custom_access'] = 'TRUE';
      $route->setRequirements($requirements);   
    }

  }

    /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Come after field_ui.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -120];
    return $events;
  }

}
