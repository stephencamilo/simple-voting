<?php

declare(strict_types=1);

namespace Drupal\simple_voting\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Form\DeleteMultipleForm;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\simple_voting\Form\VotingOptionForm;
use Drupal\simple_voting\Routing\VotingOptionHtmlRouteProvider;
use Drupal\simple_voting\VotingOptionInterface;
use Drupal\simple_voting\VotingOptionListBuilder;
use Drupal\views\EntityViewsData;

/**
 * Defines the voting option entity class.
 */
#[ContentEntityType(
  id: 'voting_option',
  label: new TranslatableMarkup('Voting option'),
  label_collection: new TranslatableMarkup('Voting options'),
  label_singular: new TranslatableMarkup('voting option'),
  label_plural: new TranslatableMarkup('voting options'),
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
  ],
  handlers: [
    'list_builder' => VotingOptionListBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'add' => VotingOptionForm::class,
      'edit' => VotingOptionForm::class,
      'delete' => ContentEntityDeleteForm::class,
      'delete-multiple-confirm' => DeleteMultipleForm::class,
    ],
    'route_provider' => [
      'html' => VotingOptionHtmlRouteProvider::class,
    ],
  ],
  links: [
    'collection' => '/admin/content/voting-option',
    'add-form' => '/voting-option/add',
    'canonical' => '/voting-option/{voting_option}',
    'edit-form' => '/voting-option/{voting_option}',
    'delete-form' => '/voting-option/{voting_option}/delete',
    'delete-multiple-form' => '/admin/content/voting-option/delete-multiple',
  ],
  admin_permission: 'administer voting',
  base_table: 'voting_option',
  label_count: [
    'singular' => '@count voting options',
    'plural' => '@count voting options',
  ],
)]
class VotingOption extends ContentEntityBase implements VotingOptionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the voting option was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the voting option was last edited.'));

    // Image field
    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setSettings([
        'file_directory' => 'voting-options',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg gif',
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }
}
