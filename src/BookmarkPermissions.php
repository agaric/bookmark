<?php
namespace Drupal\bookmark;

use Drupal\bookmark\Entity\BookmarkType;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class BookmarkPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of bookmark type permissions.
   *
   * @return array
   *   The bookmark type permissions.
   *   @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function bookmarkTypePermissions() {
    $perms = [];
    // Generate node permissions for all node types.
    foreach (BookmarkType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }
    return $perms;
  }

  /**
   * Returns a list of bookmark permissions for a given bookmark Type.
   *
   * @param \Drupal\bookmark\Entity\BookmarkType $type
   *   The bookmark type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(BookmarkType $type) {
    $type_id = $type->id();
    $type_params = ['%type_name' => $type->label()];

    return [
      "create $type_id content" => [
        'title' => $this->t('%type_name: Create new bookmarks', $type_params),
      ],
      "edit own $type_id content" => [
        'title' => $this->t('%type_name: Edit own bookmarks', $type_params),
      ],
      "edit any $type_id content" => [
        'title' => $this->t('%type_name: Edit any bookmarks', $type_params),
      ],
      "delete own $type_id content" => [
        'title' => $this->t('%type_name: Delete own bookmarks', $type_params),
      ],
      "delete any $type_id content" => [
        'title' => $this->t('%type_name: Delete any bookmarks', $type_params),
      ],
    ];
  }

}