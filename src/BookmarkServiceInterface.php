<?php

namespace Drupal\bookmark;

/**
 * Interface BookmarkServiceInterface.
 *
 * @package Drupal\bookmark
 */
interface BookmarkServiceInterface {

  /**
   * Return all the bookmarks, can also return al the bookmarks of an specific
   * bundle.
   *
   * @param string|null $bundle
   *
   * @return array
   */
  public function getAllBookmarkTypes($bundle = NULL);

  /**
   * Return the bookmarktype object.
   *
   * @param int $bookmark_type_id
   *
   * @return \Drupal\bookmark\Entity\BookmarkType
   */
  public function getBookmarkTypeById($bookmark_type_id);

  /**
   * Generate the link to bookmark an entity (Delete or an Add).
   *
   * @param \Drupal\bookmark\Entity\BookmarkTypeInterface $bookmarkType
   *   BookmarkType Object
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   *   Return a rendereable array with the link.
   */
  public function generateLink($bookmarkType, $entity);

  /**
   * Generate a link to remove the bookmark.
   *
   * @param int $bookmark_id
   *   Bookmark Id
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity where the link will be displayed.
   * @param array
   *   Return a rendereable array with the link.
   */
  public function generateDeleteLink($bookmark_id, $entity);

  /**
   * Generate a link to add the bookmark.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity where the link will be displayed.
   * @param \Drupal\bookmark\Entity\BookmarkTypeInterface $bookmarkType
   *   BookmarkType Object
   * @param array
   *   Return a rendereable array with the link.
   */
  public function generateAddLink($bookmarkType, $entity);

}
