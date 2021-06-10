<?php

namespace Drupal\social_moodle_enrollment\Plugin\Block;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Iteration requests notification' block.
 *
 * @Block(
 *   id = "iteration_requests_notification",
 *   admin_label = @Translation("Iteration requests notification"),
 * )
 */
class IterationRequestEnrollmentNotification extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Event entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface|null
   */
  protected $iteration;

  /**
   * Entity type manger.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translation;

  /**
   * Current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs SocialGroupRequestMembershipNotification.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationManager $translation
   *   The translation manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Current route match.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    TranslationManager $translation,
    RouteMatchInterface $route_match,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->iteration = social_moodle_iteration_get_current_iteration();
    $this->entityTypeManager = $entity_type_manager;
    $this->translation = $translation;
    $this->routeMatch = $route_match;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('current_route_match'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() :array {
    // No iteration? Don't bother anymore.
    if (!$this->iteration instanceof NodeInterface) {
      return [];
    }    

    $field_iteration_enrollment = $this->iteration->field_iteration_enrollment;
    if (isset($field_iteration_enrollment)) {
      $active_enrollment_method = $field_iteration_enrollment->target_id;
      // Don't continue if we don't have the correct enroll method for this iteration.
      if ($active_enrollment_method != 'request_to_enroll') {
        return [];
      }
    }

    // At this point we try to get the amount of pending requests.
    try {
      $requests = $this->entityTypeManager->getStorage('iteration_enrollment')->getQuery()
        ->condition('field_iteration.target_id', $this->iteration->id())
        ->condition('field_request_or_invite_status.value', IterationEnrollmentInterface::REQUEST_PENDING)
        ->condition('field_enrollment_status.value', '0')
        ->count()
        ->execute();

      if (!$requests) {
        return [];
      }

    

      return [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('There @link to enroll in this event.', [
          '@link' => Link::fromTextAndUrl(
            $this->translation->formatPlural($requests, 'is (1) new request', 'are (@count) new requests'),
            Url::fromRoute('view.iteration_manage_enrollment_requests.page_manage_enrollment_requests', ['node' => $this->iteration->id()])
          )->toString(),
        ]),
        '#attributes' => [
          'class' => [
            'alert',
            'alert-warning',
          ],
        ],
      ];
      
    }
    catch (InvalidPluginDefinitionException $e) {
      $this->loggerFactory->get('social_moodle_enrollment')->error($e->getMessage());
    }
    catch (PluginNotFoundException $e) {
      $this->loggerFactory->get('social_moodle_enrollment')->error($e->getMessage());
    }

    // Catch all.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $is_iteration_page = isset($this->iteration);

    // Show this block only on these specific routes.
    // We can't use the Block UI as you can't specify just the node canonical
    // route.
    $routes = [
      'entity.node.canonical',
      //'view.event_enrollments.view_enrollments',
      //'view.event_manage_enrollments.page_manage_enrollments',
      //'view.event_manage_enrollment_invites.page_manage_enrollment_invites',
      //'view.manage_enrollments.page',
      //'view.managers.view_managers',
      //'social_event_managers.add_enrollees',
      //'social_event_managers.vbo.execute_configurable',
      //'social_event_managers.vbo.confirm',
    ];

    // We have an iteration and it's part of the above list of routes.
    if ($this->iteration instanceof NodeInterface && in_array($this->routeMatch->getRouteName(), $routes, TRUE)) {
      return AccessResult::allowedIf($is_iteration_page && social_moodle_enrollment_iteration_manager_or_organizer($this->iteration));
    }

    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    // Ensure the context keeps track of the URL so we don't see the message on
    // every event.
    $contexts = Cache::mergeContexts($contexts, [
      'url',
      'user.permissions',
    ]);
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'iteration_enrollment_list:' . $this->iteration->id(),
    ]);
  }

}
