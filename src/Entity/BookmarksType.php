<?php

namespace Drupal\bookmark\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Bookmarks type entity.
 *
 * @ConfigEntityType(
 *   id = "bookmarks_type",
 *   label = @Translation("Bookmarks type"),
 *   handlers = {
 *     "list_builder" = "Drupal\bookmark\BookmarksTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bookmark\Form\BookmarksTypeForm",
 *       "edit" = "Drupal\bookmark\Form\BookmarksTypeForm",
 *       "delete" = "Drupal\bookmark\Form\BookmarksTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\bookmark\BookmarksTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "bookmarks_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "bookmarks",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/bookmarks_type/{bookmarks_type}",
 *     "add-form" = "/admin/structure/bookmarks_type/add",
 *     "edit-form" = "/admin/structure/bookmarks_type/{bookmarks_type}/edit",
 *     "delete-form" = "/admin/structure/bookmarks_type/{bookmarks_type}/delete",
 *     "collection" = "/admin/structure/bookmarks_type"
 *   }
 * )
 */
class BookmarksType extends ConfigEntityBundleBase implements BookmarksTypeInterface {

  /**
   * The Bookmarks type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Bookmarks type label.
   *
   * @var string
   */
  protected $label;

}
