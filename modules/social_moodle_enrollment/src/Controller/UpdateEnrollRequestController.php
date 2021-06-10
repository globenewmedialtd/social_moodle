<?php

namespace Drupal\social_moodle_enrollment\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Updates a pending enrollment request.
 *
 * @package Drupal\social_moodle_enrollment\Controller
 */
class UpdateEnrollRequestController extends ControllerBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * UpdateEnrollRequestController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(RequestStack $requestStack, AccountProxyInterface $currentUser) {
    $this->requestStack = $requestStack;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_user')
    );
  }

  /**
   * Updates the enrollment request.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The current iteration node.
   * @param \Drupal\social_moodle_enrollment\IterationEnrollmentInterface $iteration_enrollment
   *   The entity iteration_enrollment.
   * @param int $approve
   *   Approve the enrollment request, TRUE(1) or FALSE(0).
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return to the original destination from the current request.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateEnrollmentRequest(NodeInterface $node, IterationEnrollmentInterface $iteration_enrollment, $approve) {
    // Just some sanity checks.
    if ($node instanceof Node && !empty($iteration_enrollment)) {
      // First, lets delete all messages to keep the messages clean.
      $this->messenger()->deleteAll();
      // When the user approved,
      // we set the field_request_or_invite_status to approved.
      if ($approve === '1') {
        $iteration_enrollment->field_request_or_invite_status->value = IterationEnrollmentInterface::REQUEST_APPROVED;
        $iteration_enrollment->field_enrollment_status->value = '1';
        $this->messenger()->addStatus(t('The iteration enrollment request has been approved.'));
      }
      // When the user declined,
      // we set the field_request_or_invite_status to decline.
      elseif ($approve === '0') {
        $iteration_enrollment->field_request_or_invite_status->value = IterationEnrollmentInterface::REQUEST_OR_INVITE_DECLINED;
        $this->messenger()->addStatus(t('The iteration enrollment request has been declined.'));
      }

      // In order for the notifications to be sent correctly we're updating the
      // owner here. The account is still linked to the actual enrollee.
      // The owner is always used as the actor.
      // @see activity_creator_message_insert().
      $iteration_enrollment->setOwnerId($this->currentUser->id());

      // And finally save (update) this updated $iteration_enrollment.
      // @todo maybe think of deleting approved/declined records from the db?
      $iteration_enrollment->save();
    }

    // Get the redirect destination we're given in the request for the response.
    $destination = $this->requestStack->getCurrentRequest()->query->get('destination');

    return new RedirectResponse($destination);
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    $hasPermissionIsOwnerOrOrganizer = social_moodle_enrollment_iteration_manager_or_organizer();
    return AccessResult::allowedIf($hasPermissionIsOwnerOrOrganizer === TRUE);
  }

}
