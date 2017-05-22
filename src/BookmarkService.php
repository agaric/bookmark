<?php

namespace Drupal\bookmark;

use Drupal\bookmark\Entity\BookmarksTypeInterface;
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
    $bookmarks = $this->entityTypeManager->getStorage('bookmarks_type')->loadMultiple();
    if (isset($bundle)) {
      $bookmarks = array_filter($bookmarks, function (BookmarksTypeInterface $bookmark) use ($bundle) {
        $bundles = $bookmark->getApplicableBundles();
        return in_array($bundle, $bundles);
      });
    }

    return $bookmarks;
  }

  /**
   * {@inheritdoc}
   */
  public function getBookmarkTypeById($bookmark_id) {
    return $this->entityTypeManager->getStorage('bookmarks_type')->load($bookmark_id);
  }

  /**
   * {@inheritdoc}
   */
  public function generateLink($bookmarksType, $entity) {
    $bookmarks = $this->entityTypeManager->getStorage('bookmarks')->getQuery();
    $bookmarks->condition('url__uri', 'entity:node/' . $entity->id());
    $bookmarks->condition('type', $bookmarksType->id());
    $ids = $bookmarks->execute();

    if (!empty($ids)) {
      return $this->generateDeleteLink($bookmarksType, $entity);
    }
    else {
      return $this->generateAddLink($bookmarksType, $entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateAddLink($bookmarksType, $entity) {
    $build = [
      '#type' => 'link',
      '#title' => $bookmarksType->getLinkText(),
      '#url' => Url::fromUserInput('/admin/structure/bookmarks/add/' . $bookmarksType->id()),
      '#attributes' => [
        'class' => ['use-ajax'],
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
  public function generateDeleteLink($bookmarksType, $entity) {
    $build = [
      '#type' => 'link',
      '#title' => 'Remove from my bookmarks',
      '#url' => Url::fromUserInput('/admin/structure/bookmarks/add/' . $bookmarksType->id()),
    ];

    return $build;
  }
}
