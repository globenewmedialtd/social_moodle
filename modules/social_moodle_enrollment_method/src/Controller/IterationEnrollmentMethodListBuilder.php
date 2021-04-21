<?php

namespace Drupal\social_moodle_enrollment_method\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;


/**
 * Provides a listing of iteration_enrollment_method entities.
 *
 * List Controllers provide a list of entities in a tabular form. The base
 *
 */
class IterationEnrollmentMethodListBuilder extends ConfigEntityListBuilder {
  

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'social_moodle_enrollment_method';
  }

  /**
   * Builds the header row for the entity listing.
   *
   * @return array
   *   A render array structure of header strings.
   *
   * @see \Drupal\Core\Entity\EntityListController::render()
   */
  public function buildHeader() {
    $header['label'] = $this->t('Iteration Enrollment Method');
    $header['machine_name'] = $this->t('Machine Name');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to build the row.
   *
   * @return array
   *   A render array of the table row for displaying the entity.
   *
   * @see \Drupal\Core\Entity\EntityListController::render()
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['machine_name'] = $entity->id();

    return $row + parent::buildRow($entity);
  }


}
