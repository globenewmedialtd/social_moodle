<?php

namespace Drupal\social_moodle;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Language version entity.
 *
 * @see \Drupal\social_moodle\Entity\LanguageVersion.
 */
class LanguageVersionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\social_moodle\Entity\LanguageVersionInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished language version entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published language version entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit language version entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete language version entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add language version entities');
  }


}
