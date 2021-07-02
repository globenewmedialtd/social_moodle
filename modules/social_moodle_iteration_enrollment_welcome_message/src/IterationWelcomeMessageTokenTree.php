<?php

namespace Drupal\social_moodle_iteration_enrollment_welcome_message;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class IterationWelcomeMessageTokenTree extends ServiceProviderBase {

  public function alter ( ContainerBuilder $container ) {

    $definition = $container->getDefinition ( 'token.tree_builder' );
    $definition->setClass ( 'Drupal\social_moodle_iteration_enrollment_welcome_message\IterationWelcomeMessageTokenTreeBuilder' );
  
  }
}
