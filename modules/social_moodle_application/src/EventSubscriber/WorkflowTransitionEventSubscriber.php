<?php

namespace Drupal\social_moodle_application\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\social_moodle_application\WorkflowHelper;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowState;
use Drupal\state_machine_workflow\RevisionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\social_moodle_enrollment\Entity\IterationEnrollment;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;


/**
 * Event subscriber to handle actions on workflow-enabled entities.
 */
class WorkflowTransitionEventSubscriber implements EventSubscriberInterface {

  /**
   * The workflow helper.
   *
   * @var \Drupal\social_moodle_application\WorkflowHelperInterface
   */
  protected $workflowHelper;

  /**
   * Constructs a new WorkflowTransitionEventSubscriber object.
   *
   * @param \Drupal\social_moodle_application\WorkflowHelper $workflowHelper
   *   The workflow helper.
   */
  public function __construct(WorkflowHelper $workflowHelper) {
    $this->workflowHelper = $workflowHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'state_machine.post_transition' => 'handleAction',
    ];
  }

  /**
   * handle action based on the workflow.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The state change event.
   */
  public function handleAction(WorkflowTransitionEvent $event) {
    $entity = $event->getEntity();
    
    //kint($entity->field_application_user->entity->id());
    //exit();



    // Verify if the new state is marked as published state.
    
    $is_enrollment_state = $this->isEnrollmentState($event->getToState(), $event->getWorkflow());
    //
  
    if ($entity instanceof EntityInterface) {
      if ($is_enrollment_state) {
        // Get the referenced 
	      // Enroll
        $fields = [
          'user_id' => $entity->getOwnerId(),
          'field_iteration' => $entity->field_iteration->entity->id(),
          'field_enrollment_status' => '1',
          'field_account' => $entity->getOwnerId(),
          'field_enrollment_status' => 1
        ];

        // Create a new enrollment for the event.
        $enrollment = IterationEnrollment::create($fields);
        $enrollment->name = $entity->getOwner()->label() . ' @ ' . $entity->field_iteration->entity->label(); 
        $enrollment->save();

        \Drupal::messenger()->addStatus(t('Application successfully enrolled.'));

        // Set the date
        //$entity->set('field_date_approved_lnd', date('Y-m-d\TH:i:s', time()));
        //$entity->save();
        
      }
      else {
        
      }
    }


  }

  /**
   * Checks if a state is set as approved_lnd in a certain workflow.
   *
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowState $state
   *   The state to check.
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowInterface $workflow
   *   The workflow the state belongs to.
   *
   * @return bool
   *   TRUE if the state is set as approved_lnd in the workflow, FALSE otherwise.
   */
  protected function isEnrollmentState(WorkflowState $state, WorkflowInterface $workflow) {
    return $this->workflowHelper->isWorkflowStateEnrollment($state->getId(), $workflow);
  }

    /**
   * Checks if a state is set as approved_lnd in a certain workflow.
   *
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowState $state
   *   The state to check.
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowInterface $workflow
   *   The workflow the state belongs to.
   *
   * @return bool
   *   TRUE if the state is set as approved_lnd in the workflow, FALSE otherwise.
   */
  protected function isEmailState(WorkflowState $state, WorkflowInterface $workflow) {
    return $this->workflowHelper->isWorkflowStateEmail($state->getId(), $workflow);
  }

}
