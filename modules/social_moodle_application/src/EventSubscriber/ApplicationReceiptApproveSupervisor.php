<?php

namespace Drupal\social_moodle_application\EventSubscriber;

//use Drupal\social_moodle_mail\Mail\ApplicationMailInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends a receipt email when an application has been approved by supervisor.
 */
class ApplicationReceiptSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The order receipt mail.
   *
   * @var \Drupal\social_moodle_mail\Mail\ApplicationReceiptMailInterface
   */
  //protected $applicationReceiptMail;

  /**
   * Constructs a new ApplicationReceiptApproveSupervisor object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\social_moodle_mail\Mail\ApplicationReceiptMailInterface $applicationReceiptMail
   *   The mail handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    //$this->applicationReceiptMail = $application_receipt_mail;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = ['application_default.approve_supervisor.post_transition' => ['sendApplicationReceipt', -100]];
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
    kint($application);
   
    //$this->orderReceiptMail->send($order, $order->getEmail(), $order_type->getReceiptBcc());
   
  }

}