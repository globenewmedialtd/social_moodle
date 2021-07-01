<?php

namespace Drupal\social_moodle_enrollment\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class IterationEnrollmentController.
 *
 * @package Drupal\social_moodle_enrollment\Controller
 */
class IterationEnrollmentController extends ControllerBase {

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * SocialEventController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Function to get the decline request title.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The decline title markup.
   */
  public function getTitleDeclineRequest() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->requestStack->getCurrentRequest()->get('node');

    return $this->t('Decline enrollment request for the iteration @iteration_title', ['@iteration_title' => $node->getTitle()]);
  }

}
