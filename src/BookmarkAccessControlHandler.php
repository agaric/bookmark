<?php

namespace Drupal\bookmark;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Bookmark entity.
 *
 * @see \Drupal\bookmark\Entity\Bookmark.
 */
class BookmarkAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\bookmark\Entity\BookmarkInterface $entity */
    $uid = $entity->getOwnerId();
    switch ($operation) {
      case 'view':
        if ($uid == $account->id()) {
          return AccessResult::allowedIfHasPermission($account, 'view own bookmarks');
        } else {
          return AccessResult::allowedIfHasPermission($account, 'view any bookmarks');
        }

      case 'update':
        if ($uid == $account->id()) {
          return AccessResult::allowedIfHasPermission($account, 'edit own bookmarks');
        } else {
          return AccessResult::allowedIfHasPermission($account, 'edit any bookmarks');
        }
      case 'delete':
        if ($uid == $account->id()) {
          return AccessResult::allowedIfHasPermission($account, 'delete own bookmarks');
        } else {
          return AccessResult::allowedIfHasPermission($account, 'delete any bookmarks');
        }
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  public function createAccess($entity_bundle = NULL, AccountInterface $account = NULL, array $context = [], $return_as_object = FALSE) {
    // if account is null, lets get the current drupal user.
    if ($account == null) {
      $account = \Drupal::currentUser();
    }

    // @todo expand this check to consider all the bookmark types.
    if ($account->hasPermission('add bookmark')) {
      $result = AccessResult::allowed()->cachePerPermissions();
      return $return_as_object ? $result : $result->isAllowed();
    }

    $result = parent::createAccess($entity_bundle, $account, $context, TRUE)->cachePerPermissions();
    return $return_as_object ? $result : $result->isAllowed();
  }

}
