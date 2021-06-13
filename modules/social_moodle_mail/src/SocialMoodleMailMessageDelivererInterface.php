<?php

namespace Drupal\social_moodle_mail;

use Drupal\social_moodle_application\ApplicationInterface;

/**
 * Interface SocialMoodleMailMessageDelivererInterface.
 *
 * @package Drupal\social_moodle_mail
 */
interface SocialMoodleMailMessageDelivererInterface {

  public function existingMessages(string $machine_name);

  public function isMessageAttendee(string $machine_name);

  public function isMessageSupervisor(string $machine_name);

  public function isMessageManager(string $machine_name);

  public function isMessageLnd(string $machine_name);

  public function sendMessageAttendee(string $machine_name, ApplicationInterface $application);

  public function sendMessageSupervisor(string $machine_name, ApplicationInterface $application);

  public function sendMessageManager(string $machine_name, ApplicationInterface $application);

  public function sendMessageLnd(string $machine_name, ApplicationInterface $application);


}
