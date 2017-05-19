<?php

namespace Drupal\bookmark;

/**
 * Provides a lazy builder for flag links.
 */
interface BookmarkLinkBuilderInterface {

  /**
   * Lazy builder callback for displaying a bookmark action link.
   *
   * @param string $entity_type_id
   *   The entity type ID for which the link should be shown.
   * @param string|int $entity_id
   *   The entity ID for which the link should be shown.
   * @param string $bookmark_id
   *   The flag ID for which the link should be shown.
   *
   * @return array
   *   A render array for the action link, empty if the user does not have
   *   access.
   */
  public function build($entity_type_id, $entity_id, $bookmark_id);

}
