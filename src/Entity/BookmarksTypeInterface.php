<?php

namespace Drupal\bookmark\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an interface for defining Bookmarks type entities.
 */
interface BookmarksTypeInterface extends ConfigEntityInterface {

  /**
   * Get the link_text.
   *
   * @return string
   */
  public function getLinkText();

  /**
   * Set the link_text.
   *
   * @param string $link_text
   *  Link Text.
   */
  public function setLinkText($link_text);
}
