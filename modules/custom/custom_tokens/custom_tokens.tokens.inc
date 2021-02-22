<?php

use \Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function custom_tokens_token_info() {
  $info = [];
  $info['types']['custom_tokens'] = [
    'name'        => t('Rco Custom Tokens'), 
    'description' => t('Provides some custom tokens')
  ];
  $info['tokens']['custom_tokens']['films_list'] = [
      'name' => "Film's list",
      'description' => 'A list of the films of the current week',
      'type' => 'custom_tokens',
  ];
  return $info;
}

/**
 * Implements hook_tokens().
 */
function custom_tokens_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type == 'custom_tokens') {

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'films_list':
          $filmManager = \Drupal::service('custom_tokens.film_manager');
          $replacements[$original] = \Drupal\Core\Render\Markup::create($filmManager->getFormatedFilms());
        break;
      }
    }
  }
  return $replacements;
}
