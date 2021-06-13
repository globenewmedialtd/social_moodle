<?php

namespace Drupal\social_moodle_mail;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an application message entity type.
 */
interface ApplicationMessageInterface extends ConfigEntityInterface {


    // Attendee properties
    public function getSubjectAttendee();

    public function setSubjectAttendee(string $subject_attendee);
  
    public function getBodyAttendee();
  
    public function setBodyAttendee(array $body_attendee);

    // Supervisor properties
    public function getSubjectSupervisor();

    public function setSubjectSupervisor(string $subject_supervisor);
      
    public function getBodySupervisor();
      
    public function setBodySupervisor(array $body_supervisor);

    // Manager properties
    public function getSubjectManager();

    public function setSubjectManager(string $subject_manager);
      
    public function getBodyManager();
      
    public function setBodyManager(array $body_mananger);

    // LnD properties
    public function getSubjectLnd();

    public function setSubjectLnd(string $subject_lnd);
      
    public function getBodyLnd();
      
    public function setBodyLnd(array $body_lnd);    
  
    public function getTransition();
  
    public function setTransition(string $transition);

    public function isMessageAttendee();

    public function isMessageSupervisor();

    public function isMessageManager();

    public function isMessageLnd();
  

}
