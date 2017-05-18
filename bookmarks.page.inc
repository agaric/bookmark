<?php

/**
 * @file
 * Contains bookmarks.page.inc.
 *
 * Page callback for Bookmarks entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Bookmarks templates.
 *
 * Default template: bookmarks.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_bookmarks(array &$variables) {
  // Fetch Bookmarks Entity Object.
  $bookmarks = $variables['elements']['#bookmarks'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
