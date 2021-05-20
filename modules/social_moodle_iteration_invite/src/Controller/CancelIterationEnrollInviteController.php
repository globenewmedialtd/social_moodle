<?php

namespace Drupal\social_moodle_iteration_invite\Controller;

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
 * Cancels a pending enrollment invite.
 *
 * @package Drupal\social_moodle_iteration_invite\Controller
 */
class CancelIterationEnrollInviteController extends ControllerBase {

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
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return to the original destination from the current request.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function cancelEnrollmentInvite(NodeInterface $node, IterationEnrollmentInterface $iteration_enrollment) {
    // Just some sanity checks.
    if ($node instanceof Node && !empty($iteration_enrollment)) {
      // When the iteration owner/organizer cancelled the invite, simply remove the
      // whole iteration enrollment.
      $this->messenger()->addStatus(t('The invite has been removed.'));
      $iteration_enrollment->delete();
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
