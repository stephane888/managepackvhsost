<?php

/**
 * @file
 * Contains domain_search.page.inc.
 *
 * Page callback for Domain search entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Domain search templates.
 *
 * Default template: domain_search.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_domain_search(array &$variables) {
  // Fetch DomainSearch Entity Object.
  $domain_search = $variables['elements']['#domain_search'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
