<?php

namespace Drupal\social_moodle_iteration_invite\Plugin\Menu\LocalTask;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a local task that shows the amount of group invites.
 */
class IterationInviteLocalTask extends LocalTaskDefault implements ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Construct the UnapprovedComments object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request = NULL) {
    /** @var \Drupal\social_moodle_enrollment\IterationEnrollmentStatusHelper $enrollments */
    $enrollments = \Drupal::service('social_moodle_enrollment.status_helper');

    if ($enrollments->getAllUserIterationEnrollments(NULL)) {
      // We don't need plural because users will be redirected
      // if there is no invite.
      return $this->t('Iteration invites (@count)', ['@count' => count($enrollments->getAllUserIterationEnrollments(NULL))]);
    }

    return $this->t('Iteration invites');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = [];
    $user = $this->routeMatch->getParameter('user');

    // Add cache tags for iteration invite.
    if ($user instanceof UserInterface) {
      $tags[] = 'iteration_content_list:entity:' . $user->id();
    }

    if (is_string($user)) {
      $tags[] = 'iteration_content_list:entity:' . $user;
    }

    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }

}
