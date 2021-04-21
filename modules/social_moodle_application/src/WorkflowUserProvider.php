<?php

namespace Drupal\social_moodle_application;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Provides a user object to the state_machine workflow guard classes.
 *
 * @package Drupal\joinup_core
 */
class WorkflowUserProvider {

  /**
   * The user object to be passed in.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $account;

  /**
   * Constructs an WorkflowUserProvider service.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The service that contains the current active user.
   */
  public function __construct(AccountProxyInterface $currentUser) {
    $this->account = $currentUser;
  }

  /**
   * Returns the saved user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   A user object.
   */
  public function getUser() {
    return $this->account;
  }

  /**
   * Overrides the default user account which is the logged in user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object.
   */
  public function setUser(AccountInterface $account) {
    $this->account = $account;
  }

}
