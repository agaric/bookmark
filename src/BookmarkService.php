<?php

namespace Drupal\bookmark;

use Drupal\bookmark\Entity\BookmarkTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;

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
    $bookmarkTypes = $this->entityTypeManager->getStorage('bookmark_type')->loadMultiple();
    if (isset($bundle)) {
      $bookmarkTypes = array_filter($bookmarkTypes, function (BookmarkTypeInterface $bookmark) use ($bundle) {
        $bundles = $bookmark->getApplicableBundles();
        return in_array($bundle, $bundles);
      });
    }

    return $bookmarkTypes;
  }

  /**
   * {@inheritdoc}
   */
  public function getBookmarkTypeById($bookmark_id) {
    return $this->entityTypeManager->getStorage('bookmark_type')->load($bookmark_id);
  }

  /**
   * {@inheritdoc}
   */
  public function generateLink($bookmarkType, $entity) {
    $bookmarks = $this->entityTypeManager->getStorage('bookmark')->getQuery();
    $bookmarks->condition('url__uri', 'entity:node/' . $entity->id());
    $bookmarks->condition('type', $bookmarkType->id());
    $ids = $bookmarks->execute();

    if (!empty($ids)) {
      return $this->generateDeleteLink($bookmarkType, $entity);
    }
    else {
      return $this->generateAddLink($bookmarkType, $entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateAddLink($bookmarkType, $entity) {
    $build = [
      '#type' => 'link',
      '#title' => $bookmarkType->getLinkText(),
      '#url' => Url::fromUserInput('/bookmark/add/' . $bookmarkType->id()),
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => json_encode([
          'width' => 800,
          'height' => 500,
        ]),
      ],
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
          'core/drupal.dialog.ajax',
        ],
      ],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function generateDeleteLink($bookmarkType, $entity) {
    $build = [
      '#type' => 'link',
      '#title' => 'Remove from my bookmarks',
      '#url' => Url::fromUserInput('/bookmark/add/' . $bookmarkType->id()),
    ];

    return $build;
  }
}
