<?php

namespace Drupal\bookmark;

use Drupal\bookmark\Entity\BookmarkTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;

/**
 * Class BookmarkService.
 *
 * @package Drupal\bookmark
 */
class BookmarkService implements BookmarkServiceInterface {

  /*
   * @var EntityTypeManagerInterface
   * */
  private $entityTypeManager;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  protected $current_user;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxy $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->current_user = $current_user;
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
  public function getAllBookmarksByUser($user_id) {
    $query = $this->entityTypeManager->getStorage('bookmark')->getQuery();
    $query->condition('user_id', $user_id);
    $query->pager();
    $ids = $query->execute();
    $bookmarks = array_map(function($id) {
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
  public function generateLink($bookmarkType, $entity) {
    $bookmarks = $this->entityTypeManager->getStorage('bookmark')->getQuery();
    $bookmarks->condition('url__uri', 'entity:node/' . $entity->id());
    $bookmarks->condition('type', $bookmarkType->id());
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
  public function generateAddLink($bookmarkType, $entity) {
    /** @var \Drupal\Core\Url $url */
    $url = Url::fromRoute('entity.bookmark.add_form', ['bookmark_type' => $bookmarkType->id()]);
    $url->setOption('query', ['entity_id' => $entity->id()]);
    $build = [
      '#type' => 'link',
      '#title' => $bookmarkType->getLinkText(),
      '#url' => $url,
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-bookmark-entity-id' => $entity->id(),
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
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function generateDeleteLink($bookmark_id, $entity) {
    $url = Url::fromRoute('bookmark.actions_controller_delete', ['bookmark_id' => $bookmark_id]);
    $build = [
      '#type' => 'link',
      // @todo this title should be configurable.
      '#title' => 'Remove from my bookmarks',
      '#url' => $url,
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-bookmark-entity-id' => $entity->id(),
      ],
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];

    return $build;
  }
}
