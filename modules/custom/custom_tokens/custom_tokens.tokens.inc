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
      'description' => 'A list of films, The possible entry (? = nbr day) is an int negative or positive',
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
      list($name, $nbr) = explode(':', $name);
      switch ($name) {
        case 'films_list':
          $film = \Drupal::service('custom_tokens.film');
          $replacements[$original] = \Drupal\Core\Render\Markup::create($film->getFormatedFilms($nbr));
        break;
      }
    }
  }
  return $replacements;
}
