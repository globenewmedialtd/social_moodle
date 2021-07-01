<?php

namespace Drupal\social_moodle_iteration_enrollments_export;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Configuration override.
 */
class SocialMoodleIterationEnrollmentsExportOverrides implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];

    $config_name = 'views.view.iteration_manage_enrollments';

    if (in_array($config_name, $names)) {
      $overrides[$config_name] = [
        'display' => [
          'default' => [
            'display_options' => [
              'fields' => [
                'social_views_bulk_operations_bulk_form_iteration_enrollments_1' => [
                  'selected_actions' => [
                    'social_moodle_iteration_enrollments_export_enrollments_action' => 'social_moodle_iteration_enrollments_export_enrollments_action',
                  ],
                  'preconfiguration' => [
                    'social_moodle_iteration_enrollments_export_enrollments_action' => [
                      'label_override' => 'Export',
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ];
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'SocialEventEnrolmentsExportOverrides';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
