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
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished bookmark entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published bookmark entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit bookmark entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete bookmark entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add bookmark entities');
  }

}
