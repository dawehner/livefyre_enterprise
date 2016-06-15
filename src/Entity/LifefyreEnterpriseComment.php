<?php

namespace Drupal\livefyre_enterprise\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Defines the comment entity class.
 *
 * @ContentEntityType(
 *   id = "livefyre_enterprise_comment",
 *   label = @Translation("livefyre_enterprise_comment"),
 *   handlers = {
 *     storage = "\Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "storage_schema" = "\Drupal\livefyre_enterprise\Entity\LifefyreStorageSchema",
 *   },
 *   base_table = "livefyre_enterprise_comment",
 *   data_table = "livefyre_enterprise_comment_field_data",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "subject",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/livefyre_enterprise_comment/{livefyre_enterprise_comment}",
 *     "delete-form" = "/livefyre_enterprise_comment/{livefyre_enterprise_comment}/delete",
 *     "edit-form" = "/livefyre_enterprise_comment/{livefyre_enterprise_comment}/edit",
 *   },
 *   constraints = {
 *     "CommentName" = {}
 *   }
 * )
 */
class LifefyreEnterpriseComment extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['lf_comment_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Livefyre comment ID'))
      ->setDescription(t("The livefyre comment's id from livefyre system."))
      ->setDefaultValue(0);

    $fields['pid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parent ID'))
      ->setDescription(t('The parent comment ID if this is a reply to a comment.'))
      ->setSetting('target_type', 'comment');

    $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subject'))
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 64)
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        // Default comment body field has weight 20.
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the comment author.'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValue(0);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t("The comment author's name."))
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 60)
      ->setDefaultValue('');

    $fields['mail'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t("The comment author's email address."))
      ->setTranslatable(TRUE);

    $fields['homepage'] = BaseFieldDefinition::create('uri')
      ->setLabel(t('Homepage'))
      ->setDescription(t("The comment author's home page address."))
      ->setTranslatable(TRUE)
      // URIs are not length limited by RFC 2616, but we can only store 255
      // characters in our comment DB schema.
      ->setSetting('max_length', 255);

    $fields['hostname'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hostname'))
      ->setDescription(t("The comment author's hostname."))
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 128);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the comment was created.'))
      ->setTranslatable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the comment was last edited.'))
      ->setTranslatable(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the comment is published.'))
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['lifefyre_status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Lifefyre Publishing status'))
      ->setDescription(t('A boolean indicating whether the comment is published.'))
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['thread'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Thread place'))
      ->setDescription(t("The alphadecimal representation of the comment's place in a thread, consisting of a base 36 string prefixed by an integer indicating its length."))
      ->setSetting('max_length', 255);

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
      ->setDescription(t('The entity type to which this comment is attached.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH);

    $fields['entity_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The ID of the entity of which this comment is a reply.'))
      ->setRequired(TRUE);

    $fields['field_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Comment field name'))
      ->setDescription(t('The field name through which this comment was added.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', FieldStorageConfig::NAME_MAX_LENGTH);

    return $fields;
  }

}
