<?php

namespace Drupal\social_moodle_mail\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\social_moodle_mail\ApplicationMessageInterface;

/**
 * Defines the application message entity type.
 *
 * @ConfigEntityType(
 *   id = "application_message",
 *   label = @Translation("Application Message"),
 *   label_collection = @Translation("Application Messages"),
 *   label_singular = @Translation("application message"),
 *   label_plural = @Translation("application messages"),
 *   label_count = @PluralTranslation(
 *     singular = "@count application message",
 *     plural = "@count application messages",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\social_moodle_mail\ApplicationMessageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\social_moodle_mail\Form\ApplicationMessageForm",
 *       "edit" = "Drupal\social_moodle_mail\Form\ApplicationMessageForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "application_message",
 *   admin_permission = "administer application_message",
 *   links = {
 *     "collection" = "/admin/config/social_moodle/application-message",
 *     "add-form" = "/admin/config/social_moodle/application-message/add",
 *     "edit-form" = "/admin/config/social_moodle/application-message/{application_message}",
 *     "delete-form" = "/admin/config/social_moodle/application-message/{application_message}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "transition" = "transition",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "transition",
 *     "subject_attendee",
 *     "body_attendee",
 *     "subject_supervisor",
 *     "body_supervisor",
 *     "subject_manager",
 *     "body_manager",
 *     "subject_lnd",
 *     "body_lnd",
 *     "uuid"
 *   }
 * )
 */
class ApplicationMessage extends ConfigEntityBase implements ApplicationMessageInterface {

  /**
   * The application message ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The application_message subject_attendee.
   *
   * @var string
   */
  protected $subject_attendee;  

  /**
   * The application message body_attendee.
   *
   * @var array
   */
  protected $body_attendee;

  /**
   * The application_message subject_supervisor.
   *
   * @var string
   */
  protected $subject_supervisor;

  /**
   * The application message body_supervisor.
   *
   * @var array
   */
  protected $body_supervisor;

  /**
   * The application_message subject_manager.
   *
   * @var string
   */
  protected $subject_manager;

  /**
   * The application message body_manager.
   *
   * @var array
   */
  protected $body_manager;

  /**
   * The application_message subject_lnd.
   *
   * @var string
   */
  protected $subject_lnd;

  /**
   * The application message body_lnd.
   *
   * @var array
   */
  protected $body_lnd;

  /**
   * The application_message transition.
   *
   * @var string
  */
  protected $transition;

  /**
   * {@inheritdoc}
   */
  public function getSubjectAttendee() {
    return $this->subject_attendee;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubjectAttendee(string $subject_attendee) {
    $this->subject_attendee = $subject_attendee;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBodyAttendee() {
    return $this->body_attendee;
  }

  /**
   * {@inheritdoc}
   */
  public function setBodyAttendee(array $body_attendee) {
    $this->body_attendee = $body_attendee;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubjectSupervisor() {
    return $this->subject_supervisor;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubjectSupervisor(string $subject_supervisor) {
    $this->subject_supervisor = $subject_supervisor;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBodySupervisor() {
    return $this->body_supervisor;
  }

  /**
   * {@inheritdoc}
   */
  public function setBodySupervisor(array $body_supervisor) {
    $this->body_supervisor = $body_supervisor;
    return $this;
  }  

  /**
   * {@inheritdoc}
   */
  public function getSubjectManager() {
    return $this->subject_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubjectManager(string $subject_manager) {
    $this->subject_manager = $subject_manager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBodyManager() {
    return $this->body_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setBodyManager(array $body_manager) {
    $this->body_manager = $body_manager;
    return $this;
  }  

  /**
   * {@inheritdoc}
   */
  public function getSubjectLnd() {
    return $this->subject_lnd;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubjectLnd(string $subject_lnd) {
    $this->subject_lnd = $subject_lnd;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBodyLnd() {
    return $this->body_lnd;
  }

  /**
   * {@inheritdoc}
   */
  public function setBodyLnd(array $body_lnd) {
    $this->body_lnd = $body_lnd;
    return $this;
  }    

  /**
   * {@inheritdoc}
   */
  public function getTransition() {
    return $this->transition;
  }

  /**
   * {@inheritdoc}
   */
  public function setTransition(string $transition) {
    $this->transition = $transition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isMessageAttendee() {

    $subject = $this->subject_attendee;
    $body = $this->body_attendee;

    if (isset($subject) && isset($body)) {
      if (!empty($subject) && !empty($body['value'])) {
        return TRUE;
      }
    }

    return FALSE;
    
  }

  /**
   * {@inheritdoc}
   */
  public function isMessageSupervisor() {

    $subject = $this->subject_supervisor;
    $body = $this->body_supervisor;

    if (isset($subject) && isset($body)) {
      if (!empty($subject) && !empty($body['value'])) {
        return TRUE;
      }
    }

    return FALSE;
    
  }

  /**
   * {@inheritdoc}
   */
  public function isMessageManager() {

    $subject = $this->subject_manager;
    $body = $this->body_manager;

    if (isset($subject) && isset($body)) {
      if (!empty($subject) && !empty($body['value'])) {
        return TRUE;
      }
    }

    return FALSE;
    
  }

  /**
   * {@inheritdoc}
   */
  public function isMessageLnd() {

    $subject = $this->subject_lnd;
    $body = $this->body_lnd;

    if (isset($subject) && isset($body)) {
      if (!empty($subject) && !empty($body['value'])) {
        return TRUE;
      }
    }

    return FALSE;
    
  }

}
