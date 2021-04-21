<?php

namespace Drupal\social_moodle_enrollment\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Iteration enrollment entities.
 */
class IterationEnrollmentViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['iteration_enrollment_field_data']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Iteration enrollment'),
      'help' => $this->t('The Iteration enrollment ID.'),
    ];

    return $data;
  }

}
