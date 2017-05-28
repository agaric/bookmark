<?php

namespace Drupal\bookmark\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Bookmark type entities.
 */
interface BookmarkTypeInterface extends ConfigEntityInterface {

  /**
   * Get the link_text.
   *
   * @return string
   *   Return the text which will be displayed in the link.
   */
  public function getLinkText();

  /**
   * Set the link_text.
   *
   * @param string $link_text
   *   Link Text.
   */
  public function setLinkText($link_text);

  /**
   * Get the bundles property.
   */
  public function getBundles();

  /**
   * Set the bundles property.
   *
   * @param array $bundles
   *   The bundles allowed in this bookmarkType.
   */
  public function setBundles(array $bundles);

  /**
   * Return the bundles where this bookmarktype can be used.
   *
   * @return array
   *   Get all the bundles allowed for this bookmarkType.
   */
  public function getApplicableBundles();

}
