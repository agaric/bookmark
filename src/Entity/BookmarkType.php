<?php

namespace Drupal\bookmark\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Bookmark type entity.
 *
 * @ConfigEntityType(
 *   id = "bookmark_type",
 *   label = @Translation("Bookmark type"),
 *   handlers = {
 *     "list_builder" = "Drupal\bookmark\bookmarkTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bookmark\Form\bookmarkTypeForm",
 *       "edit" = "Drupal\bookmark\Form\bookmarkTypeForm",
 *       "delete" = "Drupal\bookmark\Form\bookmarkTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\bookmark\bookmarkTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "bookmark_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "bookmark",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/bookmark_type/{bookmark_type}",
 *     "add-form" = "/admin/structure/bookmark_type/add",
 *     "edit-form" = "/admin/structure/bookmark_type/{bookmark_type}/edit",
 *     "delete-form" = "/admin/structure/bookmark_type/{bookmark_type}/delete",
 *     "collection" = "/admin/structure/bookmark_type"
 *   }
 * )
 */
class BookmarkType extends ConfigEntityBundleBase implements bookmarkTypeInterface {

  /**
   * The Bookmark type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Bookmark type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Bookmark link text.
   *
   * @var string
   */
  protected $link_text = '';

  /**
   * The bundles this bookmark applies to.
   *
   * This may be an empty array to indicate all bundles apply.
   *
   * @var array
   */
  protected $bundles = [];

  /**
   * {@inheritdoc}
   */
  public function getLinkText() {
    return $this->link_text;
  }

  /**
   * {@inheritdoc}
   */
  public function setLinkText($link_text) {
    $this->link_text = $link_text;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundles() {
    return $this->bundles;
  }

  /**
   * {@inheritdoc}
   */
  public function setBundles($bundles) {
    $this->link_text = $bundles;
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicableBundles() {
    $bundles = $this->getBundles();

    if (empty($bundles)) {
      // If the setting is empty,return all bundle names.
      /** @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info_service */
      $bundle_info_service = \Drupal::service('entity_type.bundle.info');
      $bundle_info = $bundle_info_service->getBundleInfo('Node');
      $bundles = array_keys($bundle_info);
    }
    return $bundles;

  }

}
