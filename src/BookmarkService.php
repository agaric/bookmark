<?php

namespace Drupal\bookmark;

use Drupal\bookmark\Entity\BookmarksTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;

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
  public function getBookmarkById($bookmark_id) {
    return $this->entityTypeManager->getStorage('bookmarks_type')->load($bookmark_id);
  }
}
