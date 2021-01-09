<?php

namespace Drupal\destroy\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Destroy Block.
 *
 * @Block(
 *   id = "destroy_block",
 *   admin_label = @Translation("Destroy block")
 * )
 */
class Destroy extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration, 
    $plugin_id, 
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

  	return [
      '#theme'  => "destroy_template",
      '#cache'  => ['max-age' => 0]
    ];
  }
}
