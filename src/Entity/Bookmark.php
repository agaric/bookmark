<?php

namespace Drupal\bookmark\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;

/**
 * Defines the Bookmark entity.
 *
 * @ingroup bookmark
 *
 * @ContentEntityType(
 *   id = "bookmark",
 *   label = @Translation("Bookmark"),
 *   bundle_label = @Translation("Bookmark type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bookmark\BookmarkListBuilder",
 *     "views_data" = "Drupal\bookmark\Entity\BookmarkViewsData",
 *     "translation" = "Drupal\bookmark\BookmarkTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\bookmark\Form\BookmarkForm",
 *       "add" = "Drupal\bookmark\Form\BookmarkForm",
 *       "edit" = "Drupal\bookmark\Form\BookmarkForm",
 *       "delete" = "Drupal\bookmark\Form\BookmarkDeleteForm",
 *     },
 *     "access" = "Drupal\bookmark\BookmarkAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\bookmark\BookmarkHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "bookmark",
 *   data_table = "bookmark_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer bookmark",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/bookmark/{bookmark}",
 *     "add-page" = "/admin/content/bookmark/add",
 *     "add-form" = "/bookmark/content/add/{bookmark_type}",
 *     "edit-form" = "/bookmark/{bookmark}/edit",
 *     "delete-form" = "/bookmark/{bookmark}/delete",
 *     "collection" = "/admin/content/bookmark",
 *   },
 *   bundle_entity_type = "bookmark_type",
 *   field_ui_base_route = "entity.bookmark_type.edit_form"
 * )
 */
class Bookmark extends ContentEntityBase implements BookmarkInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $url = $this->get('url')->getValue();
    $name = Url::fromUri($url[0]['uri']);
    $name->setAbsolute(TRUE);
    $this->setName($name->toString());

    // If It is not empty the title lets use it for the entity label.
    if (!empty($url[0]['title'])) {
      $this->setName($url[0]['title']);
    }
  }

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
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
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
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the flagging user. This is recorded for both global and personal flags.'))
      ->setSettings([
        'target_type' => 'user',
        'default_value' => 0,
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Bookmark entity.'))
      ->setSettings([
        'max_length' => 250,
        'text_processing' => 0,
      ]);

    $fields['url'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Url'))
      ->setDescription(t('The name of the page or site.'))
      ->setSettings([
        'max_length' => 250,
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
      ->setDescription(t('A boolean indicating whether the Bookmark is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
