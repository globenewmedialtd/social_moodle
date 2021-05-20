<?php

namespace Drupal\social_moodle_iteration_invite\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Accepts or declines an iteration enrollment invite.
 *
 * @package Drupal\social_moodle_iteration_invite\Controller
 */
class UserIterationEnrollInviteController extends CancelIterationEnrollInviteController {

  /**
   * {@inheritdoc}
   */
  public function updateEnrollmentInvite(IterationEnrollmentInterface $iteration_enrollment, $accept_decline) {
    // Just some sanity checks.
    if (!empty($iteration_enrollment)) {
      // When the user accepted the invite,
      // we set the field_request_or_invite_status to approved.
      if ($accept_decline === '1') {
        $iteration_enrollment->field_request_or_invite_status->value = IterationEnrollmentInterface::INVITE_ACCEPTED_AND_JOINED;
        $iteration_enrollment->field_enrollment_status->value = '1';
        $statusMessage = $this->getMessage($iteration_enrollment, $accept_decline);
        if (!empty($statusMessage)) {
          // Lets delete all messages to keep the messages clean.
          $this->messenger()->deleteAll();
          $this->messenger()->addStatus($statusMessage);
        }
      }
      // When the user declined,
      // we set the field_request_or_invite_status to decline.
      elseif ($accept_decline === '0') {
        $iteration_enrollment->field_request_or_invite_status->value = IterationEnrollmentInterface::REQUEST_OR_INVITE_DECLINED;
        $statusMessage = $this->getMessage($iteration_enrollment, $accept_decline);
        if (!empty($statusMessage)) {
          // Lets delete all messages to keep the messages clean.
          $this->messenger()->deleteAll();
          $this->messenger()->addStatus($statusMessage);
        }
      }

      // And finally save (update) this updated $iteration_enrollment.
      // @todo maybe think of deleting approved/declined records from the db?
      $iteration_enrollment->save();

      // Invalidate cache.
      $tags = [];
      $tags[] = 'enrollment:' . $iteration_enrollment->field_iteration->value . '-' . $this->currentUser->id();
      $tags[] = 'iteration_content_list:entity:' . $this->currentUser->id();
      Cache::invalidateTags($tags);
    }

    // Get the redirect destination we're given in the request for the response.
    $destination = Url::fromRoute('view.user_iteration_invites.page_user_iteration_invites', ['user' => $this->currentUser->id()])->toString();

    return new RedirectResponse($destination);
  }

  /**
   * Generates a nice message for the user.
   *
   * @param \Drupal\social_moodle_enrollment\IterationEnrollmentInterface $iteration_enrollment
   *   The iteration enrollment.
   * @param string $accept_decline
   *   The approve (1) or decline (0) number.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   The message.
   */
  public function getMessage(IterationEnrollmentInterface $iteration_enrollment, $accept_decline) {
    $statusMessage = NULL;
    // Get the target iteration id.
    $target_iteration_id = $iteration_enrollment->get('field_iteration')->getValue();
    // Get the iteration node.
    $iteration = $this->entityTypeManager()->getStorage('node')->load($target_iteration_id[0]['target_id']);

    // Only if we have an iteration, we perform the rest of the logic.
    if (!empty($iteration)) {
      // Build the link to the iteration node.
      $link = Link::createFromRoute($this->t('@node', ['@node' => $iteration->get('title')->value]), 'entity.node.canonical', ['node' => $iteration->id()])
        ->toString();
      // Nice message with link to the iteration the user has enrolled in.
      if (!empty($iteration->get('title')->value) && $accept_decline === '1') {
        $statusMessage = $this->t('You have accepted the invitation for the @iteration iteration.', ['@iteration' => $link]);
      }
      // Nice message with link to the iteration the user has respectfully declined.
      elseif (!empty($iteration->get('title')->value) && $accept_decline === '0') {
        $statusMessage = $this->t('You have declined the invitation for the @iteration iteration.', ['@iteration' => $link]);
      }
    }

    return $statusMessage;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // Get the parameter from the request that has been done.
    $user_parameter = $this->requestStack->getCurrentRequest()->attributes->get('user');
    // Check if it's the same that is in the current session's account.
    if ($account->id() === $user_parameter) {
      return AccessResult::allowed();
    }
    return AccessResult::neutral();
  }

}
