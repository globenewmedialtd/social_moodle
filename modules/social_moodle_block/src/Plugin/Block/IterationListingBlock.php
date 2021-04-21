<?php

namespace Drupal\social_moodle_block\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;

/**
 * Provides a 'IterationListingBlock' block.
 *
 * @Block(
 *  id = "iteration_listing_block",
 *  admin_label = @Translation("Iteration listing block"),
  * )
 */
class IterationListingBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a GroupHeroBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $group = \Drupal::routeMatch()->getParameter('group');
    $group_id = $group->id();
    //kint($group);

    $nids = social_moodle_block_get_group_content($group_id);


    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $node_storage->loadMultiple($nids);


    $attributes = [
            'class' => [
              'use-ajax',
              'js-form-submit',
              'form-submit',
              'btn',
              'btn-accent',
              'btn-lg',
            ],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => json_encode([
              'title' => t('Request'),
              'width' => 'auto',
            ]),
          ];

    foreach ($nodes as $node) {

      $links = [
        [
          'title' => 'Apply',
          'url' => Url::fromRoute('social_moodle_enrollment.request_application_dialog',['node' => $node->id()]),
          'attributes' => $attributes
        ],
        [
          'title' => 'Nominate',
          'url' => Url::fromRoute('social_moodle_enrollment.request_nomination_dialog', ['node' => $node->id()]),
          'attributes' => $attributes
       ],
     ];

       $link_list = [ 
        '#theme' => 'links',
        '#links' => $links,              
       ];

   

      $node_content[$node->id()] = [
		'#theme' => 'social_moodle_block_iteration_listing',
  		'#title' => $node->title->value,
		'#start' => $node->field_iteration_date->value,
		'#end' => $node->field_iteration_date_end->value,
                '#links' => $link_list,

              ];  		 

	
    }



     






      
      

      $build['content'] = $node_content;
      $build['#attached']['library'][] = 'core/drupal.dialog.ajax';
      // Cache tags.
      $build['#cache']['tags'][] = 'iteration_listing_block:' . $group->id();
    
    // Cache contexts.
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowed();
  }



}
