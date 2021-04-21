<?php

namespace Drupal\social_moodle_enrollment\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\social_moodle_enrollment\IterationEnrollmentInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Cache\Cache;
use Drupal\node\NodeInterface;

/**
 * Defines the Iteration enrollment entity.
 *
 * @ingroup social_moodle_enrollment
 *
 * @ContentEntityType(
 *   id = "iteration_enrollment",
 *   label = @Translation("Iteration enrollment"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\social_moodle_enrollment\IterationEnrollmentListBuilder",
 *     "views_data" = "Drupal\social_moodle_enrollment\Entity\IterationEnrollmentViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\social_moodle_enrollment\Form\IterationEnrollmentForm",
 *       "add" = "Drupal\social_moodle_enrollment\Form\IterationEnrollmentForm",
 *       "edit" = "Drupal\social_moodle_enrollment\Form\IterationEnrollmentForm",
 *       "delete" = "Drupal\social_moodle_enrollment\Form\IterationEnrollmentDeleteForm",
 *     },
 *     "access" = "Drupal\social_moodle_enrollment\IterationEnrollmentAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\social_moodle_enrollment\IterationEnrollmentHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "iteration_enrollment",
 *   data_table = "iteration_enrollment_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer iteration enrollment entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/iteration_enrollment/{iteration_enrollment}",
 *     "add-form" = "/admin/structure/iteration_enrollment/add",
 *     "edit-form" = "/admin/structure/iteration_enrollment/{iteration_enrollment}/edit",
 *     "delete-form" = "/admin/structure/iteration_enrollment/{iteration_enrollment}/delete",
 *     "collection" = "/admin/structure/iteration_enrollment",
 *   },
 *   field_ui_base_route = "iteration_enrollment.settings"
 * )
 */
class IterationEnrollment extends ContentEntityBase implements IterationEnrollmentInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  

  /**
   * {@inheritdoc}
   */
/*
  public function preSave(EntityStorageInterface $storage) {
    $tags = [
      'iteration_content_list:user:' . $this->getAccount(),
      'iteration_enrollment_list:' . $this->getFieldValue('field_event', 'target_id'),
    ];
    Cache::invalidateTags($tags);
    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
/*
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    if (!empty($entities)) {
      $tags = [];
      foreach ($entities as $enrollment) {
        $tags = [
          'iteration_content_list:user:' . $enrollment->getAccount(),
          'iteration_enrollment_list:' . $enrollment->getFieldValue('field_event', 'target_id'),
        ];
      }
      Cache::invalidateTags($tags);
    }
  }
  
  */

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }


  /**
   * {@inheritdoc}
   */  
  public function getAccount() {
    return $this->get('field_account')->target_id;
  }
  

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NodeInterface::PUBLISHED : NodeInterface::NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Iteration enrollment entity.'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Iteration enrollment entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Iteration enrollment entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getDefaultEntityOwner')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Iteration enrollment entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Iteration enrollment is published.'))
      ->setDefaultValue(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Iteration enrollment entity.'))
      ->setDisplayOptions('form', [
        'type' => 'language_select',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
