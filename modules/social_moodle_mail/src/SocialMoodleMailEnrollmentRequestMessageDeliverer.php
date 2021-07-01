<?php

namespace Drupal\social_moodle_mail;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\social_group\SocialGroupHelperService;


/**
 * SocialMoodleMailEnrollmentRequestMessageDeliverer is a service
 * that fetches available messages for the enrollment request workflow
 */

class SocialMoodleMailEnrollmentRequestMessageDeliverer {

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
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The group helper service.
   *
   * @var \Drupal\social_group\SocialGroupHelperService
   */
  protected $groupHelperService;

  /**
   * Constructs a new SocialMoodleMailMessageDeliverer object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, QueueFactory $queue_factory, ConfigFactoryInterface $config_factory, SocialGroupHelperService $group_helper_service) {
    $this->storage = $entity_type_manager;
    $this->queue = $queue_factory;
    $this->configFactory = $config_factory;
    $this->groupHelperService = $group_helper_service;
  }

  /**
   * Adds enrollee to group when approved request
   *
   * @param string $nid
   *   The node id (id) of the iteration.
   * @param string $uid
   *   The user id (id) of the iteration.
   */
  public function addEnrollee(string $nid, string $uid) {

    // Get Account
    $account = $this->storage->getStorage('user')->load($uid);

    // We need to get the group via groupHelperService
    $gid_from_entity = $this->groupHelperService->getGroupFromEntity([
      'target_type' => 'node',
      'target_id' => $nid,
    ]);

    if ($gid_from_entity !== NULL) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = $this->storage->getStorage('group')->load($gid_from_entity);
    }

    if ($group instanceof GroupInterface) {

      $is_member = $group->getMember($account) instanceof GroupMembershipLoaderInterface;

      // Only add member if not already a member
      if (!$is_member) {    
        $group->addMember($account);  
      }

    }

  }

  public function sendDeclineMessage(string $nid, string $uid) {

    // Create $users array
    $users = [
      $uid => $uid
    ];

    // We need to get the group via groupHelperService
    $gid_from_entity = $this->groupHelperService->getGroupFromEntity([
      'target_type' => 'node',
      'target_id' => $nid,
    ]);
    
    if ($gid_from_entity !== NULL) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = $this->storage->getStorage('group')->load($gid_from_entity);
    }

    if ($group instanceof GroupInterface) {

      // Get data from application and put it into a queue
      $data['message_id'] = 'decline'; 
      $data['users'] = $users;
      $data['group'] = $group;

      // Put the $data in the queue item.
      /** @var \Drupal\Core\Queue\QueueInterface $queue */
      $queue = $this->queue->get('social_moodle_mail_enrollment_request_email_queue');
      $queue->createItem($data);


    }

  }

  public function sendApproveMessage(string $nid, string $uid) {

    // Create $users array
    $users = [
      $uid => $uid
    ];

    // We need to get the group via groupHelperService
    $gid_from_entity = $this->groupHelperService->getGroupFromEntity([
      'target_type' => 'node',
      'target_id' => $nid,
    ]);
    
    if ($gid_from_entity !== NULL) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = $this->storage->getStorage('group')->load($gid_from_entity);
    }

    if ($group instanceof GroupInterface) {

      // Get data from application and put it into a queue
      $data['message_id'] = 'approve'; 
      $data['users'] = $users;
      $data['group'] = $group;

      // Put the $data in the queue item.
      /** @var \Drupal\Core\Queue\QueueInterface $queue */
      $queue = $this->queue->get('social_moodle_mail_enrollment_request_email_queue');
      $queue->createItem($data);


    }

  }
  

}
