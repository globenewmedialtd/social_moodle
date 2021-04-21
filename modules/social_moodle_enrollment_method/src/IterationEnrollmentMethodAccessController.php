<?php

namespace Drupal\social_moodle_enrollment_method;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the entity.
 */
class IterationEnrollmentMethodAccessController extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // The $opereration parameter tells you what sort of operation access is
    // being checked for.
    if ($operation == 'view') {
      return AccessResult::allowed();
    }
    
    return parent::checkAccess($entity, $operation, $account);
  }

}
