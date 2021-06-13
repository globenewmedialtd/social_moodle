<?php

namespace Drupal\social_moodle_application\EventSubscriber;

use Drupal\social_moodle_mail\SocialMoodleMailMessageDelivererInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends a receipt email when an application has been approved by lnd.
 */
class ApplicationReceiptApproveLndSubscriber implements EventSubscriberInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $storage;

  /**
   * The message deliverer.
   *
   * @var \Drupal\social_moodle_mail\SocialMoodleMailMessageDelivererInterface
   */
  protected $message_deliverer;

  /**
   * Constructs a new ApplicationReceiptApproveSupervisor object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\social_moodle_mail\SocialMoodleMailMessageDelivererInterface $message_deliverer
   *   The mail handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, 
                              SocialMoodleMailMessageDelivererInterface $message_deliverer ) {
    $this->storage = $entity_type_manager;
    $this->message_deliverer = $message_deliverer;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = ['application.approve_lnd.post_transition' => ['sendApplicationReceipt', -100]];
    return $events;
  }

  /**
   * Sends an application receipt email.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The event we subscribed to.
   */
  public function sendApplicationReceipt(WorkflowTransitionEvent $event) {
    /** @var \Drupal\social_moodle_application\ApplicationInterface $application */
    $application = $event->getEntity();
    $machine_name = 'approved_lnd';    
    
    $is_attendee = $this->message_deliverer->isMessageAttendee($machine_name);
    $is_supervisor = $this->message_deliverer->isMessageSupervisor($machine_name);
    $is_manager = $this->message_deliverer->isMessageManager($machine_name);
    $is_lnd = $this->message_deliverer->isMessageLnd($machine_name);
    
    // Send Message to Attendee
    if ($is_attendee) {
      $this->message_deliverer->sendMessageAttendee($machine_name,$application);
    }

    // Send Message to Supervisor
    if ($is_supervisor) {
      $this->message_deliverer->sendMessageSupervisor($machine_name,$application);
    }
    
    // Send Message to Managers
    if ($is_manager) {
      $this->message_deliverer->sendMessageManager($machine_name,$application);
    }
    
    // Send Message to Lnd
    if ($is_lnd) {
      $this->message_deliverer->sendMessageLnd($machine_name,$application);
    }
    
  }

}
