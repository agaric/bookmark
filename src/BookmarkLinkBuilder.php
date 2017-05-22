<?php

namespace Drupal\bookmark;

use Drupal\bookmark\BookmarkServiceInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\bookmark\BookmarkLinkBuilderInterface;

/**
 * Provides a lazy builder for Bookmark links.
 */
class BookmarkLinkBuilder implements BookmarkLinkBuilderInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The bookmark service.
   *
   * @var \Drupal\bookmark\BookmarkService
   */
  protected $bookmarkService;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\flag\FlagServiceInterface $flag_service
   *   The flag service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, BookmarkServiceInterface $bookmark_service) {
    $this->entityTypeManager = $entity_manager;
    $this->bookmarkService = $bookmark_service;
  }

  /**
   * {@inheritdoc}
   */
  public function build($entity_type_id, $entity_id, $bookmark_id) {
    $entity = $this->entityTypeManager->getStorage($entity_type_id)->load($entity_id);
    $bookmarkType = $this->bookmarkService->getBookmarkById($bookmark_id);

    return $bookmarkType->generateLink($entity);
  }

}
