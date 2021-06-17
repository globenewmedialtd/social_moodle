<?php

namespace Drupal\social_moodle_iteration;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\social_moodle_iteration\SocialMoodleIterationEnrollmentInfoInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxy;


/**
 * SocialMoodleIterationEnrollmentInfo is a service
 * that fetches available iterations from the database
 */
class SocialMoodleIterationEnrollmentInfo implements SocialMoodleIterationEnrollmentInfoInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $storage;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;  

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $current_user;    

  /**
   * Constructs a new SocialMoodleMailMessageDeliverer object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $connection, AccountProxy $current_user) {
    $this->storage = $entity_type_manager;
    $this->connection = $connection;
    $this->current_user = $current_user;
  }

  /**
   * Delivers Array of node ids.
   *
   * @param Drupal\group\Entity\GroupInterface $group
   *   The group object.
   */
  public function getDefaultIterationRecords(GroupInterface $group) {
    
    // Retrieve Storrage
    $database = $this->connection;
    $query = $database->select('node_field_data', 'nfd');
    $query->addField('nfd', 'nid');
    $query->join('group_content_field_data', 'gfd', 'gfd.entity_id = nfd.nid');
    $query->condition('nfd.type', 'iteration');
    $query->condition('gfd.gid', $group->id(), '=');
    $query->distinct();
    $iteration = $query->execute()->fetchCol();

    if (isset($iteration) && !empty($iteration)) {
      return $iteration;
    }
        
    return FALSE;

  }

  /**
   * Delivers Array of node ids.
   *
   * @param Drupal\group\Entity\GroupInterface $group
   *   The group object.
   */
  public function getEnrolledIterationRecords(GroupInterface $group) {
    
    $uid = $this->current_user->id();

    // Retrieve Storrage
    $database = $this->connection;
    $query = $database->select('node_field_data', 'nfd');
    $query->addField('nfd', 'nid');
    $query->join('group_content_field_data', 'gcfd', 'gcfd.entity_id = nfd.nid');
    $query->join('iteration_enrollment__field_iteration', 'iefi', 'iefi.field_iteration_target_id = nfd.nid');
    $query->join('iteration_enrollment_field_data', 'iefd', 'iefd.id = iefi.entity_id');
    $query->join('iteration_enrollment__field_enrollment_status', 'iefs', 'iefs.entity_id = iefd.id');
    $query->leftJoin('iteration_enrollment__field_account', 'iefa', 'iefa.entity_id = iefd.id');
    $query->condition('nfd.type', 'iteration');
    $query->condition('gcfd.gid', $group->id(), '=');
    $query->condition('iefs.field_enrollment_status_value', '1', '=');
    $query->condition('iefa.field_account_target_id', $uid, '=');
    $query->distinct();

    $iteration = $query->execute()->fetchCol();

    if (isset($iteration) && !empty($iteration)) {
      return $iteration;
    }
        
    return FALSE;

  }  

  /**
   * Delivers Array of node ids.
   *
   * @param Drupal\group\Entity\GroupInterface $group
   *   The group object.
   */
  public function getPendingIterationRecords(GroupInterface $group) {

    $uid = $this->current_user->id();

    // Retrieve Storrage
    $database = $this->connection;
    $query = $database->select('node_field_data', 'nfd');
    $query->addField('nfd', 'nid');
    $query->join('group_content_field_data', 'gcfd', 'gcfd.entity_id = nfd.nid');
    $query->join('iteration_enrollment__field_iteration', 'iefi', 'iefi.field_iteration_target_id = nfd.nid');
    $query->join('iteration_enrollment_field_data', 'iefd', 'iefd.id = iefi.entity_id');
    $query->join('iteration_enrollment__field_enrollment_status', 'iefs', 'iefs.entity_id = iefd.id');
    $query->leftJoin('iteration_enrollment__field_account', 'iefa', 'iefa.entity_id = iefd.id');
    $query->condition('nfd.type', 'iteration');
    $query->condition('gcfd.gid', $group->id(), '=');
    $query->condition('iefs.field_enrollment_status_value', '0', '=');
    $query->condition('iefa.field_account_target_id', $uid, '=');
    $query->distinct();

    $iteration = $query->execute()->fetchCol();

    if (isset($iteration) && !empty($iteration)) {
      return $iteration;
    }
        
    return FALSE;

  } 

}