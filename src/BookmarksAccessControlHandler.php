<?php

namespace Drupal\bookmark;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Bookmarks entity.
 *
 * @see \Drupal\bookmark\Entity\Bookmarks.
 */
class BookmarksAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\bookmark\Entity\BookmarksInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished bookmarks entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published bookmarks entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit bookmarks entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete bookmarks entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add bookmarks entities');
  }

}
