<?php

namespace Drupal\bookmark\Plugin\Block;

use Drupal\bookmark\BookmarkService;
use Drupal\bookmark\Entity\BookmarkType;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a bookmark Link block.
 *
 * @Block(
 *   id = "bookmark_link_block",
 *   admin_label = @Translation("Bookmark Link Block"),
 *   category = @Translation("Bookmark"),
 *   deriver = "Drupal\bookmark\Plugin\Derivative\BookmarkLinkBlock"
 * )
 */
class BookmarkLinkBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Bookmark Service.
   *
   * @var \Drupal\bookmark\BookmarkService
   */
  protected $bookmarkService;

  /**
   * Bookmark Type Object.
   *
   * @var \Drupal\bookmark\entity\BookmarkTypeInterface
   */
  protected $bookmarkType;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BookmarkService $bookmark_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->bookmarkService = $bookmark_service;
    $this->bookmarkType = BookmarkType::load($this->getDerivativeId());
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('bookmark')
    );
  }

  /**
   * Build the content for bookmark link block.
   */
  public function build() {
    return $this->bookmarkService->generateAddLink($this->bookmarkType);
  }

}
