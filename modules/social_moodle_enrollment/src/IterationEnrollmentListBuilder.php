<?php

namespace Drupal\social_moodle_enrollment;

use Drupal\Core\Link;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Iteration enrollment entities.
 *
 * @ingroup social_moodle_enrollment
 */
class IterationEnrollmentListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = $this->t('Iteration enrollment ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    /** @var \Drupal\social_moodle_enrollment\Entity\IterationEnrollment $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::fromTextAndUrl($entity->label(), new Url(
      'entity.iteration_enrollment.edit_form', [
        'iteration_enrollment' => $entity->id(),
      ]
    ));
    return $row + parent::buildRow($entity);
  }

}
