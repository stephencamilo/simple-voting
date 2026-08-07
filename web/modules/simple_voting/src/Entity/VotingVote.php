<?php

declare(strict_types=1);

namespace Drupal\simple_voting\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\simple_voting\VotingVoteInterface;
use Drupal\simple_voting\VotingVoteListBuilder;
use Drupal\views\EntityViewsData;

/**
 * Defines the voting vote entity class.
 */
#[ContentEntityType(
    id: 'voting_vote',
    label: new TranslatableMarkup('Voting vote'),
    label_collection: new TranslatableMarkup('Voting votes'),
    label_singular: new TranslatableMarkup('voting vote'),
    label_plural: new TranslatableMarkup('voting votes'),
    entity_keys: [
      'id' => 'id',
      'uuid' => 'uuid',
    ],
    handlers: [
      'list_builder' => VotingVoteListBuilder::class,
      'views_data' => EntityViewsData::class,
    ],
    admin_permission: 'administer voting',
    base_table: 'voting_vote',
    constraints: [
      'UniqueVote' => [],
    ],
    label_count: [
      'singular' => '@count voting vote',
      'plural' => '@count voting votes',
    ],
)]
class VotingVote extends ContentEntityBase implements VotingVoteInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setSetting('target_type', 'voting_question')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['option_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Option'))
      ->setSetting('target_type', 'voting_option')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Voted on'))
      ->setDescription(t('The time when the vote was cast.'));

    return $fields;
  }

}
