<?php

namespace Drupal\social_moodle_iteration_managers\EventSubscriber;

use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Builds up the routes of iteration management forms.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Constructs the service with DI.
   *
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandler $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
  }

  /**
   * Returns a set of route objects.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   A route collection.
   */
  public function routes() {
    $collection = new RouteCollection();

    if ($this->moduleHandler->moduleExists('views_bulk_operations')) {
      // Add routes for Management View
      $route = new Route(
        '/node/{node}/all-iteration-enrollments/configure-action',
        [
          '_form' => '\Drupal\social_moodle_iteration_managers\Form\SocialMoodleIterationManagersManagementViewsBulkOperationsConfigureAction',
          '_title' => 'Configure action',
          'view_id' => 'iteration_manage_enrollments',
          'display_id' => 'page_manage_enrollments',
        ],
        [
          '_views_bulk_operation_access' => 'TRUE',
        ]
      );
      $collection->add('social_moodle_iteration_managers.vbo.execute_configurable', $route);

      $route = new Route(
        '/node/{node}/all-iteration-enrollments/confirm-action',
        [
          '_form' => '\Drupal\social_moodle_iteration_managers\Form\SocialMoodleIterationManagersViewsBulkOperationsConfirmAction',
          '_title' => 'Confirm action',
          'view_id' => 'iteration_manage_enrollments',
          'display_id' => 'page_manage_enrollments',
        ],
        [
          '_views_bulk_operation_access' => 'TRUE',
        ]
      );
      $collection->add('social_moodle_iteration_managers.vbo.confirm', $route);

      // Add routes for Enrollment Request View
      $route = new Route(
        '/node/{node}/all-iteration-enrollment-requests/configure-action',
        [
          '_form' => '\Drupal\social_moodle_iteration_managers\Form\SocialMoodleIterationManagersEnrollmentRequestsViewsBulkOperationsConfigureAction',
          '_title' => 'Configure action',
          'view_id' => 'iteration_manage_enrollment_requests',
          'display_id' => 'page_manage_enrollment_requests',
        ],
        [
          '_views_bulk_operation_access' => 'TRUE',
        ]
      );
      $collection->add('social_moodle_iteration_managers.request.vbo.execute_configurable', $route);

      $route = new Route(
        '/node/{node}/all-iteration-enrollment-requests/confirm-action',
        [
          '_form' => '\Drupal\social_moodle_iteration_managers\Form\SocialMoodleIterationManagersEnrollmentRequestsViewsBulkOperationsConfirmAction',
          '_title' => 'Confirm action',
          'view_id' => 'iteration_manage_enrollment_requests',
          'display_id' => 'page_manage_enrollment_requests',
        ],
        [
          '_views_bulk_operation_access' => 'TRUE',
        ]
      );
      $collection->add('social_moodle_iteration_managers.request.vbo.confirm', $route);      

    }

    return $collection;
  }

}
