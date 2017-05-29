<?php

namespace Drupal\bookmark;

use Drupal\bookmark\Entity\BookmarkTypeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface BookmarkServiceInterface.
 *
 * @package Drupal\bookmark
 */
interface BookmarkServiceInterface {

  /**
   * Return all the bookmarks bundle.
   *
   * @param string|null $bundle
   *   Bundle of the bookmarkType.
   *
   * @return array
   *   Return an array with all the bookmarkTypes.
   */
  public function getAllBookmarkTypes($bundle = NULL);

  /**
   * Return the bookmarktype object.
   *
   * @param int $bookmark_type_id
   *   The BookmarkType ID.
   *
   * @return \Drupal\bookmark\Entity\BookmarkType
   *   Return the BookmarkType object.
   */
  public function getBookmarkTypeById($bookmark_type_id);

  /**
   * Return the bookmarks of an specific user.
   *
   * @param int $uid
   *   User's id.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Return all the user's bookmark.
   */
  public function getAllBookmarksByUser($uid);

  /**
   * Generate the link to bookmark an entity (Delete or an Add).
   *
   * @param \Drupal\bookmark\Entity\BookmarkTypeInterface $bookmarkType
   *   BookmarkType Object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity where this link will be displayed.
   *
   * @return array
   *   Return a rendereable array with the link.
   */
  public function generateLink(BookmarkTypeInterface $bookmarkType, EntityInterface $entity);

  /**
   * Generate a link to remove the bookmark.
   *
   * @param int $bookmark_id
   *   Bookmark Id.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity where the link will be displayed.
   *
   * @return array
   *   Return a rendereable array with the link.
   */
  public function generateDeleteLink($bookmark_id, EntityInterface $entity);

  /**
   * Generate a link to add the bookmark.
   *
   * @param \Drupal\bookmark\Entity\BookmarkTypeInterface $bookmarkType
   *   BookmarkType Object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity where the link will be displayed.
   *
   * @return array
   *   Return a rendereable array with the link.
   */
  public function generateAddLink(BookmarkTypeInterface $bookmarkType, EntityInterface $entity);

}
