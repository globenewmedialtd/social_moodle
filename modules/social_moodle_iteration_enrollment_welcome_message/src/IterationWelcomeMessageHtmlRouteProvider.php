<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * Provides routes for Welcome Message entities.
 *
 * @see Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class IterationWelcomeMessageHtmlRouteProvider extends AdminHtmlRouteProvider {


  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    
    // Provide your custom entity routes here.
    return $collection;
  }

}
