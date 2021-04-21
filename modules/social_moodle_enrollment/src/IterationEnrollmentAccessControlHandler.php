<?php

namespace Drupal\social_moodle_enrollment;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Iteration enrollment entity.
 *
 * @see \Drupal\social_moodle_enrollment\Entity\EventEnrollment.
 */
class IterationEnrollmentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\social_moodle_enrollment\IterationEnrollmentInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished iteration enrollment entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published iteration enrollment entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit iteration enrollment entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete iteration enrollment entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add iteration enrollment entities');
  }

}
