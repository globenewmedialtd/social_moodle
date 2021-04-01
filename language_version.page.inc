<?php

/**
 * @file
 * Contains language_version.page.inc.
 *
 * Page callback for Language version entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Language version templates.
 *
 * Default template: language_version.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_language_version(array &$variables) {
  // Fetch LanguageVersion Entity Object.
  $language_version = $variables['elements']['#language_version'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
