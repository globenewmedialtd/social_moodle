<?php

namespace Drupal\social_moodle_mail;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\social_moodle_mail\SocialMoodleMailMessageDelivererInterface;
use Drupal\social_moodle_mail\ApplicationMessageInterface;
use Drupal\social_moodle_application\ApplicationInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Queue\QueueFactory;


/**
 * SocialMoodleMailMessageDeliverer is a service
 * that fetches available messages from the config entity
 * ApplicationMessage. 
 */

class SocialMoodleMailMessageDeliverer implements SocialMoodleMailMessageDelivererInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $storage;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;  

  /**
   * Constructs a new SocialMoodleMailMessageDeliverer object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, QueueFactory $queue_factory) {
    $this->storage = $entity_type_manager;
    $this->queue = $queue_factory;
  }

  /**
   * Delivers boolean TRUE or FALSE for messages.
   *
   * @param string $machine_name
   *   The machine_name (id) of the message.
   */
  public function existingMessages(string $machine_name) {
    
    // Retrieve Storrage
    $storage = $this->storage->getStorage('application_message');
    $message = $storage->load($machine_name);

    if ($message instanceof ApplicationMessageInterface) {
      if ($message->isMessageAttendee() ||
        $message->isMessageSupervisor() ||
        $message->isMessageManager() ||
        $message->isMessageLnd()) {
          return TRUE;
      }
    }
    
    return FALSE;

  }

  public function isMessageAttendee(string $machine_name) {
    // Retrieve Storrage
    $storage = $this->storage->getStorage('application_message');
    $message = $storage->load($machine_name);

    if ($message instanceof ApplicationMessageInterface) {

      return $message->isMessageAttendee(); 

    }
    
    return FALSE;

  }

  public function isMessageSupervisor(string $machine_name) {
    // Retrieve Storrage
    $storage = $this->storage->getStorage('application_message');
    $message = $storage->load($machine_name);

    if ($message instanceof ApplicationMessageInterface) {

      return $message->isMessageSupervisor(); 

    }
    
    return FALSE;

  } 

  public function isMessageManager(string $machine_name) {
    // Retrieve Storrage
    $storage = $this->storage->getStorage('application_message');
    $message = $storage->load($machine_name);

    if ($message instanceof ApplicationMessageInterface) {

      return $message->isMessageManager(); 

    }
    
    return FALSE;

  }
  
  public function isMessageLnd(string $machine_name) {
    // Retrieve Storrage
    $storage = $this->storage->getStorage('application_message');
    $message = $storage->load($machine_name);

    if ($message instanceof ApplicationMessageInterface) {

      return $message->isMessageLnd(); 

    }
    
    return FALSE;

  }  

  /**
   * Sends a message to a queue
   *
   * @param string $machine_name
   *   The machine_name (id) of the message.
   * @param \Drupal\social_moodle_application\ApplicationInterface $application
   *   The application the message is for. 
   */  

  public function sendMessageAttendee(string $machine_name, ApplicationInterface $application) {

    // Get the group entity from application
    $group = $application->field_group->entity;
    // Get the user id of the application user
    $uid = $application->getOwnerId();

    // Create $users array
    $users = [
      $uid => $uid
    ];

    // Get data from application and put it into a queue
    $data['message_id'] = $machine_name;
    $data['message_type'] = 'attendee'; 
    $data['users'] = $users;
    $data['group'] = $group;

    // Put the $data in the queue item.
    /** @var \Drupal\Core\Queue\QueueInterface $queue */
    $queue = $this->queue->get('social_moodle_mail_email_queue');
    $queue->createItem($data);


  }

  /**
   * Sends a message to a queue
   *
   * @param string $machine_name
   *   The machine_name (id) of the message.
   * @param \Drupal\social_moodle_application\ApplicationInterface $application
   *   The application the message is for. 
   */  

  public function sendMessageSupervisor(string $machine_name, ApplicationInterface $application) {

    // Get the group entity from application
    $group = $application->field_group->entity;
    // Get the user id of the supervisor user
    $uid = $application->field_supervisor->target_id;

    // Create $users array
    $users = [
      $uid => $uid
    ];

    // Get data from application and put it into a queue
    $data['message_id'] = $machine_name;
    $data['message_type'] = 'supervisor'; 
    $data['users'] = $users;
    $data['group'] = $group;

    // Put the $data in the queue item.
    /** @var \Drupal\Core\Queue\QueueInterface $queue */
    $queue = $this->queue->get('social_moodle_mail_email_queue');
    $queue->createItem($data);


  }

  /**
   * Sends a message to a queue
   *
   * @param string $machine_name
   *   The machine_name (id) of the message.
   * @param \Drupal\social_moodle_application\ApplicationInterface $application
   *   The application the message is for. 
   */  

  public function sendMessageManager(string $machine_name, ApplicationInterface $application) {

    // Get the group entity from application
    $group = $application->field_group->entity;
    // Create $users array
    $users = $this->getGroupManagers($group);

    if (isset($users) && !empty($users)) {
      // Get data from application and put it into a queue
      $data['message_id'] = $machine_name;
      $data['message_type'] = 'manager'; 
      $data['users'] = $users;
      $data['group'] = $group;

      // Put the $data in the queue item.
      /** @var \Drupal\Core\Queue\QueueInterface $queue */
      $queue = $this->queue->get('social_moodle_mail_email_queue');
      $queue->createItem($data);

    }

  }

  /**
   * Sends a message to a queue
   *
   * @param string $machine_name
   *   The machine_name (id) of the message.
   * @param \Drupal\social_moodle_application\ApplicationInterface $application
   *   The application the message is for. 
   */
  public function sendMessageLnd(string $machine_name, ApplicationInterface $application) {

    // Get the group entity from application
    $group = $application->field_group->entity;
    // Create $users array
    $users = $this->getLndUsers();

    if (isset($users) && !empty($users)) {
      // Get data from application and put it into a queue
      $data['message_id'] = $machine_name;
      $data['message_type'] = 'lnd'; 
      $data['users'] = $users;
      $data['group'] = $group;

      // Put the $data in the queue item.
      /** @var \Drupal\Core\Queue\QueueInterface $queue */
      $queue = $this->queue->get('social_moodle_mail_email_queue');
      $queue->createItem($data);

    }

  }

  protected function getLndUsers() {
    
    $users = \Drupal::entityQuery('user')
              ->condition('roles', 'lnd', 'CONTAINS')
              ->execute();

    if (isset($users) && !empty($users)) {
      return $users;
    }

    return FALSE;

  }

  protected function getGroupManagers(GroupInterface $group) {

    $users = FALSE;

    $members = $group->getMembers();
    foreach ($members as $member) {      
      if ($member->hasPermission('edit group')) {
        $uid = $member->getUser()->id();
        $users[$uid] = $uid;
      }   
    }

    return $users;

  }
  

}