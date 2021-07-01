<?php

namespace Drupal\social_moodle_iteration_enrollments_export\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Drupal\social_user_export\Plugin\Action\ExportUser;

/**
 * Exports an iteration enrollment accounts to CSV.
 *
 * @Action(
 *   id = "social_moodle_iteration_enrollments_export_enrollments_action",
 *   label = @Translation("Export the selected iteration enrollments to CSV"),
 *   type = "iteration_enrollment",
 *   confirm = TRUE,
 *   confirm_form_route_name = "social_moodle_iteration_managers.vbo.confirm",
 * )
 */
class ExportIterationEnrollments extends ExportUser {

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    /** @var \Drupal\social_moodle_enrollment\IterationEnrollmentInterface $entity */
    foreach ($entities as &$entity) {
      $entity = $this->getAccount($entity);
    }

    parent::executeMultiple($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object instanceof IterationEnrollmentInterface) {
      $access = $this->getAccount($object)->access('view', $account, TRUE);
    }
    else {
      $access = AccessResult::forbidden();
    }

    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   *
   * To make sure the file can be downloaded, the path must be declared in the
   * download pattern of the social user export module.
   *
   * @see social_user_export_file_download()
   */
  protected function generateFilePath() : string {
    $hash = md5(microtime(TRUE));
    return 'export-iteration-enrollments-' . substr($hash, 20, 12) . '.csv';
  }

  /**
   * Extract user entity from iteration enrollment entity.
   *
   * @param \Drupal\social_moodle_enrollment\IterationEnrollmentInterface $entity
   *   The Iteration enrollment.
   *
   * @return \Drupal\user\UserInterface
   *   The user.
   */
  public function getAccount(IterationEnrollmentInterface $entity) {
    $accounts = $entity->field_account->referencedEntities();
    return reset($accounts);
  }

}
