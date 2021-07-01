<?php

namespace Drupal\social_moodle_iteration_managers\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;

/**
 * Approve iteration enrollment entity action.
 *
 * @Action(
 *   id = "social_moodle_iteration_managers_approve_iteration_enrollment_action",
 *   label = @Translation("Approve selected iteration enrollment entities"),
 *   type = "iteration_enrollment",
 *   confirm = TRUE,
 *   confirm_form_route_name = "social_moodle_iteration_managers.request.vbo.confirm",
 * )
 */
class IterationEnrollmentRequestApproveAction extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\social_moodle_enrollment\IterationEnrollmentInterface $entity */
    $entity->field_request_or_invite_status->value = IterationEnrollmentInterface::REQUEST_APPROVED;
    $entity->field_enrollment_status->value = '1';
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access = AccessResult::forbidden();

    if ($object instanceof IterationEnrollmentInterface) {
      //$access = $object->access('delete', $account, TRUE);

      $iteration_id = $object->getFieldValue('field_iteration', 'target_id');
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($iteration_id);
      // Also Iteratiopn organizers can do this.
      if ($node instanceof NodeInterface && social_moodle_enrollment_iteration_manager_or_organizer($node)) {
        $access = AccessResult::allowedIf($object instanceof IterationEnrollmentInterface);
      }
    }

    return $return_as_object ? $access : $access->isAllowed();
  }

}
