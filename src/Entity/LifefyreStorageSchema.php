<?php

namespace Drupal\livefyre_enterprise\Entity;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

class LifefyreStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    $schema['livefyre_enterprise_comment_field_data']['indexes'] += [
      'comment__status_pid' => ['pid', 'status'],
      'comment__num_new' => [
        'entity_id',
        'entity_type',
        'comment_type',
        'status',
        'created',
        'cid',
        'thread',
      ],
      'comment__entity_langcode' => [
        'entity_id',
        'entity_type',
        'comment_type',
        'default_langcode',
      ],
    ];

    return $schema;
  }

}
