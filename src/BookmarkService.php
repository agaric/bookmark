<?php

namespace Drupal\bookmark;

use Drupal\bookmark\Entity\BookmarkTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Class BookmarkService.
 *
 * @package Drupal\bookmark
 */
class BookmarkService implements BookmarkServiceInterface {

  /**
   * EntityTypeManager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllBookmarkTypes($bundle = NULL) {
    $bookmarkTypes = $this->entityTypeManager->getStorage('bookmark_type')->loadMultiple();
    if (isset($bundle)) {
      $bookmarkTypes = array_filter($bookmarkTypes, function (BookmarkTypeInterface $bookmark) use ($bundle) {
        $bundles = $bookmark->getApplicableBundles();
        return in_array($bundle, $bundles);
      });
    }

    return $bookmarkTypes;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllBookmarksByUser($uid) {
    $query = $this->entityTypeManager->getStorage('bookmark')->getQuery()->accesCheck(TRUE);
    $query->condition('uid', $uid);
    $query->pager();
    $ids = $query->execute();
    $bookmarks = array_map(function ($id) {
      return $this->entityTypeManager->getStorage('bookmark')->load($id);
    }, $ids);
    return $bookmarks;
  }

  /**
   * {@inheritdoc}
   */
  public function getBookmarkTypeById($bookmark_type_id) {
    return $this->entityTypeManager->getStorage('bookmark_type')->load($bookmark_type_id);
  }

  /**
   * {@inheritdoc}
   */
  public function generateLink(BookmarkTypeInterface $bookmarkType, EntityInterface $entity, AccountInterface $account) {
    $bookmarks = $this->entityTypeManager->getStorage('bookmark')->getQuery()->accessCheck(TRUE);
    $bookmarks->condition('url__uri', 'entity:node/' . $entity->id());
    $bookmarks->condition('type', $bookmarkType->id());
    $bookmarks->condition('uid', $account->id());
    $ids = $bookmarks->execute();

    if (!empty($ids)) {
      $id = array_shift($ids);
      return $this->generateDeleteLink($id, $entity);
    }
    else {
      return $this->generateAddLink($bookmarkType, $entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateAddLink(BookmarkTypeInterface $bookmarkType, EntityInterface $entity = NULL) {
    /** @var \Drupal\Core\Url $url */
    $url = Url::fromRoute('entity.bookmark.add_form', ['bookmark_type' => $bookmarkType->id()]);
    $query = ['use_ajax' => 1];
    if (!is_null($entity)) {
      $query['entity_id'] = $entity->id();
    }
    $url->setOption('query', $query);

    $build = [
      '#type' => 'link',
      '#title' => $bookmarkType->getLinkText(),
      '#url' => $url,
      '#attributes' => [
        'class' => ['use-ajax', 'content--action', 'content--action--bookmark'],
        'data-bookmark-entity-id' => ($entity instanceof  EntityInterface) ? $entity->id() : "",
        'data-dialog-type' => 'modal',
        'data-dialog-options' => json_encode([
          'width' => 800,
          'height' => 500,
        ]),
      ],
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
          'core/drupal.dialog.ajax',
        ],
      ],
    ];
    return [
      '#theme' => 'bookmark_link',
      '#link' => $build,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function generateDeleteLink($bookmark_id, EntityInterface $entity) {
    $url = Url::fromRoute('bookmark.actions_controller_delete', ['bookmark_id' => $bookmark_id]);
    $build = [
      '#type' => 'link',
      // @todo this title should be configurable.
      '#title' => 'Remove from my bookmarks',
      '#url' => $url,
      '#attributes' => [
        'class' => ['use-ajax', 'content--action', 'content--action--bookmark-delete'],
        'data-bookmark-entity-id' => $entity->id(),
      ],
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];

    return [
      '#theme' => 'bookmark_link',
      '#link' => $build,
    ];
  }

}
