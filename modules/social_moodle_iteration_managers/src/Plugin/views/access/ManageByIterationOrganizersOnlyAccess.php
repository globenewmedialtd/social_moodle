<?php

namespace Drupal\social_moodle_iteration_managers\Plugin\views\access;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Manage by iteration organizers only access plugin.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "manage_by_iteration_organizers_only",
 *   title = @Translation("Manage by iteration organizers only"),
 *   help = @Translation("Access to the iteration manage all enrollment page.")
 * )
 */
class ManageByIterationOrganizersOnlyAccess extends AccessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->isAuthenticated();
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_custom_access', '\Drupal\social_moodle_iteration_managers\Controller\IterationManagersController::access');
  }

}
