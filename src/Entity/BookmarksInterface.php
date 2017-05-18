<?php

namespace Drupal\bookmark\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Bookmarks entities.
 *
 * @ingroup bookmark
 */
interface BookmarksInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Bookmarks name.
   *
   * @return string
   *   Name of the Bookmarks.
   */
  public function getName();

  /**
   * Sets the Bookmarks name.
   *
   * @param string $name
   *   The Bookmarks name.
   *
   * @return \Drupal\bookmark\Entity\BookmarksInterface
   *   The called Bookmarks entity.
   */
  public function setName($name);

  /**
   * Gets the Bookmarks creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Bookmarks.
   */
  public function getCreatedTime();

  /**
   * Sets the Bookmarks creation timestamp.
   *
   * @param int $timestamp
   *   The Bookmarks creation timestamp.
   *
   * @return \Drupal\bookmark\Entity\BookmarksInterface
   *   The called Bookmarks entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Bookmarks published status indicator.
   *
   * Unpublished Bookmarks are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Bookmarks is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Bookmarks.
   *
   * @param bool $published
   *   TRUE to set this Bookmarks to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\bookmark\Entity\BookmarksInterface
   *   The called Bookmarks entity.
   */
  public function setPublished($published);

}
