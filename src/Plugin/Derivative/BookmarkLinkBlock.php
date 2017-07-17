<?php

namespace Drupal\bookmark\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bookmark\BookmarkServiceInterface;

/**
 * Provides block plugin definitions for bookmark blocks.
 *
 * @see \Drupal\bookmark\Plugin\Block\BookmarkLinkBlock
 */
class BookmarkLinkBlock extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Bookmark Service.
   *
   * @var \Drupal\bookmark\BookmarkServiceInterface
   */
  protected $bookmark;

  /**
   * BookmarkLinkBlock constructor.
   *
   * @param \Drupal\bookmark\BookmarkServiceInterface $bookmark
   *   Bookmark Service.
   */
  public function __construct(BookmarkServiceInterface $bookmark) {
    $this->bookmark = $bookmark;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('bookmark')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $bookmarks = $this->bookmark->getAllBookmarkTypes();

    /** @var \Drupal\bookmark\Entity\BookmarkType $bookmark */
    foreach ($bookmarks as $bookmark) {
      $block_id = $bookmark->id();
      $this->derivatives[$block_id] = $base_plugin_definition;
      $this->derivatives[$block_id]['admin_label'] = "Bookmark:" . $bookmark->label();
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
