<?php

namespace Drupal\match_point\Plugin\Block;

use Drupal\match_point\MatchPointManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Match point Winner Block.
 *
 * @Block(
 *   id = "match_point_winner_block",
 *   admin_label = @Translation("Match point winner block")
 * )
 */
class Winner extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  const DEFAULT_TEMPLATE   = 'match_point_winner_template';

  /**
   * @var Drupal\match_point\MatchPointManagerInterface
   */
  protected $matchPointManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration, 
    $plugin_id, 
    $plugin_definition, 
    MatchPointManager $matchPointManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->matchPointManager  = $matchPointManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('match_point.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($winner = $this->matchPointManager->getWinner()) {
      return [
        '#theme'  => self::DEFAULT_TEMPLATE,
        '#user'   => $winner
      ];
    }
  }
}
