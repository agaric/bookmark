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
   * @param int $bookmark_id
   *
   * @return \Drupal\bookmark\Entity\BookmarksType
   */
  public function getBookmarkById($bookmark_id);

}
