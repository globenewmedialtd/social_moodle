<?php 

namespace Drupal\social_moodle_application\Plugin\Action;

use Drupal\social_moodle_application\ApplicationInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Approves an application by LnD.
 *
 * @Action(
 *   id = "social_moodle_application_approve_lnd",
 *   label = @Translation("Approve by LnD"),
 *   type = "application",
 *   requirements = {
 *     "_permission" = "use approve_lnd transition in application_default",
 *   }
 * )
 */
class ApproveLndTransition extends ActionBase {

  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {

    if ($object instanceof ApplicationInterface) {
      /** @var \Drupal\state_machine\Plugin\Field\FieldType\StateItem $state_field */
      $state_field = $object->getState();
      $transitions = $state_field->getTransitions();
      $access = AccessResult::allowedIf(!empty($transitions['approve_lnd']));
    }
    else {
      $access = AccessResult::forbidden();
    }

    return $return_as_object ? $access : $access->isAllowed();

  }

  public function execute($entity = NULL) {
    try {
      if ($entity instanceof ApplicationInterface) {
        /** @var \Drupal\state_machine\Plugin\Field\FieldType\StateItem $state_field */
        $state_field = $entity->get('field_state')->first();
        try {
          $state_field->applyTransitionById('approve_lnd');
          $entity->save();
        } catch (\InvalidArgumentException $e) {
          if ($e->getMessage() === sprintf('Unknown transition ID "%s".', 'approve_lnd')) {
            //$entity->setFulfilled();
            //$entity->save();
          }
        }
      }
    }
    catch (\Throwable $e) {
      \Drupal::logger('social_moodle_application')->error($e->getMessage());
      \Drupal::messenger()->addError($e->getMessage());
    }
  }

}

