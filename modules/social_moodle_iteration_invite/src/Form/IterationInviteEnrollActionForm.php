<?php

namespace Drupal\social_moodle_iteration_invite\Form;

use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Drupal\social_moodle_enrollment\Form\IterationEnrollActionForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;

/**
 * Class IterationInviteEnrollActionForm.
 *
 * @package Drupal\social_moodle_iteration_invite\Form
 */
class IterationInviteEnrollActionForm extends IterationEnrollActionForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    static $count = 0;
    $count++;

    return 'iteration_invite_enroll_action_form_' . $count;

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    //$form = parent::buildForm($form, $form_state);
    $current_user = $this->currentUser;
    $uid = $current_user->id();
    $invite_only = FALSE;

    // We check if the node is placed in a Group I am a member of. If not,
    // we are not going to build anything.
    if (!empty($nid)) {
      if (!is_object($nid) && !is_null($nid)) {      
        $node = $this->entityTypeManager
          ->getStorage('node')
          ->load($nid);       
      }
    }

    if (isset($node)) {

      $enrollment_methods = $node->field_iteration_enrollment->referencedEntities();
      if (isset($enrollment_methods)) {
        foreach ($enrollment_methods as $method) {
          if ($method->id === 'invite_only') {
            $invite_only = TRUE;
          }
        }
      }

    }


    if (!$current_user->isAnonymous()) {
      $conditions = [
        'field_account' => $uid,
        'field_iteration' => $nid,
      ];
      $enrollments = $this->entityStorage->loadByProperties($conditions);

      // If the iteration is invite only and you have not been invited, return.
      // Unless you are the node owner or organizer.
      if (empty($enrollments)) {
        if ($invite_only && social_moodle_enrollment_iteration_manager_or_organizer() === FALSE) {
          return [];
        }
      }
      elseif ($enrollment = array_pop($enrollments)) {
        $enroll_request_status = $enrollment->field_request_or_invite_status->value;
        $enroll_status = $enrollment->field_enrollment_status->value;

        if ($enroll_request_status == '5' || $enroll_status == '1' ) {
          // We are enrolled
          $options_course_link = [
            'query' => [
              'idnumber' => $nid
            ]
          ];
  
          $form['buttons']['course_link'] = [
            '#type' => 'link',
            '#title' => $this->t('Show course'),
            '#url' => Url::fromUri('internal:/moodle/redirect.php',$options_course_link),
            '#attributes' => [
              'class' => [
                'js-form-submit',
                'form-submit',
                'btn',
                'btn-accent',
                'btn-lg',
             ]
            ]
          ];

          $form['buttons']['enrolled'] = [
            '#type' => 'button',
            '#value' => $this->t('Enrolled'),
            '#disabled' => TRUE,
            '#attributes' => [
              'class' => [
                'js-form-submit',
                'form-submit',
                'btn',
                'btn-accent',
                'btn-lg',
              ]
            ]
          ];


        }
        

        // If user got invited perform actions.
        if ($enroll_request_status == '4') {

          $submit_text = $this->t('Accept Invitation');

          $form['enroll_for_this_iteration'] = [
            '#type' => 'submit',
            '#value' => $submit_text,
            '#name' => 'accept_invite',
          ];

          // Extra attributes needed for when a user is logged in.
          // This will make sure the button acts like a dropdown.
          $form['enroll_for_this_iteration']['#attributes'] = [
            'class' => [
              'btn',
              'btn-accent brand-bg-accent',
              'btn-lg btn-raised',
              'dropdown-toggle',
              'waves-effect',
            ],
          ];

          // We need a hidden element for later usage.
          $form['iteration'] = [
            '#type' => 'hidden',
            '#value' => $nid,
          ];

          //$form['decline_invite'] = [
            //'#type' => 'submit',
            //'#value' => '',
           // '#name' => 'decline_invite',
         // ];

          

          //$form['#attached']['library'][] = 'social_moodle_enrollment/form_submit';

        }
      }
    }

    // For AN users it can be rendered on a Public iteration with
    // invite only as option. Let's make it similar to a Group experience
    // where there is no button rendered.
    // We unset it here because in the parent form and this form
    // a lot of times this button get's overridden.
    //if ($current_user->isAnonymous()) {
      //unset($form['enroll_for_this_event']);
    //}

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $operation = $form_state->getValue('operation');
    $current_user = $this->currentUser;
    $uid = $current_user->id();
    $nid = $form_state->getValue('iteration') ?? $this->routeMatch->getRawParameter('node');

    $conditions = [
      'field_account' => $uid,
      'field_iteration' => $nid,
    ];

    $enrollments = $this->entityStorage->loadByProperties($conditions);

    // @todo also clear the breadcrumb cachetags.
    // Invalidate cache for our enrollment cache tag in
    // social_event_node_view_alter().
    $tags = [];
    $tags[] = 'iteration_enrollment:' . $nid . '-' . $uid;
    $tags[] = 'iteration_content_list:entity:' . $uid;
    Cache::invalidateTags($tags);

    if ($enrollment = array_pop($enrollments)) {
      // Only trigger when the user is invited.
      if ($enrollment->field_request_or_invite_status
        && (int) $enrollment->field_request_or_invite_status->value === IterationEnrollmentInterface::INVITE_PENDING_REPLY) {
        // Delete any messages since it would show a 'successful enrollment'.
        $this->messenger()->deleteAll();
        // Accept the invite.
        $enrollment->field_enrollment_status->value = '1';
        $enrollment->field_request_or_invite_status->value = IterationEnrollmentInterface::INVITE_ACCEPTED_AND_JOINED;

        // If decline is chosen, set invite to declined.
        if ($operation === 'decline') {
          // Delete any messages since it would show a 'successful enrollment'.
          $this->messenger()->deleteAll();
          $enrollment->field_enrollment_status->value = '0';
          $enrollment->field_request_or_invite_status->value = IterationEnrollmentInterface::REQUEST_OR_INVITE_DECLINED;
        }
        $enrollment->save();
      }
    }
  }

}
