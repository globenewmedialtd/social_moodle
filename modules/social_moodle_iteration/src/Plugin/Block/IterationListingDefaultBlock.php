<?php

namespace Drupal\social_moodle_iteration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\social_moodle_iteration\SocialMoodleIterationEnrollmentInfoInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Provides a 'IterationListingDefaultBlock' block.
 *
 * @Block(
 *  id = "iteration_listing_default_block",
 *  admin_label = @Translation("Iteration Listing Default block"),
 * )
 */
class IterationListingDefaultBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The service EnrollmentInfo.
   *
   * @var \Drupal\social_moodle_iteration\SocialMoodleIterationEnrollmentInfoInterface
   */
  protected $enrollment_info;  

  /**
   * TopicAddBlock constructor.
   *
   * @param array $configuration
   *   The given configuration.
   * @param string $plugin_id
   *   The given plugin id.
   * @param mixed $plugin_definition
   *   The given plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Drupal\social_moodle_iteration\SocialMoodleIterationEnrollmentInfoInterface $enrollment_info
   *   The enrollment info service. 
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $routeMatch, SocialMoodleIterationEnrollmentInfoInterface $enrollment_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
    $this->enrollment_info = $enrollment_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('social_moodle_iteration.enrollment_info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $group = $this->routeMatch->getParameter('group');

    if ($group instanceof GroupInterface) {
      $default = $this->enrollment_info->getDefaultIterationRecords($group);
      $enrolled = $this->enrollment_info->getEnrolledIterationRecords($group);
      $pending = $this->enrollment_info->getPendingIterationRecords($group);
      if ($default) {
        foreach($default as $key => $value) {
          if ($enrolled) {
            if (in_array($value,$enrolled)) {
              unset($default[$key]);
            }
          }
          if ($pending) {
            if (in_array($value,$pending)) {
              unset($default[$key]);
            }
          }
        }
      
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($default);
        $build['content'] = \Drupal::entityTypeManager()->getViewBuilder('node')->viewMultiple($nodes, 'iteration_listing');
        $build['#attributes']['class'][] = 'card__block';
       


      }
    } 
    
    return $build;
    
  }

  /**
   * @return int
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
