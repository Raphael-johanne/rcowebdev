<?php

namespace Drupal\match_point\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Level controller.
 */
class LevelController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Construct
   *
   * @param \Drupal\Core\Database\Connection $databaseConnection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
  * overview
   */
  public function overview() {

    $rows = [];

    $header = [
      [
        'data'  => $this->t('ID'),
        'field' => 'mpl.id',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('From'),
        'field' => 'mpl.name',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('To'),
        'field' => 'mpl.points',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Points'),
        'field' => 'mpl.points',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    $query = $this->connection->select('match_point_level', 'mpl')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('mpl', [
      'id',
      'from',
      'to',
      'points'
    ]);
    
    $levels = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();

    foreach ($levels as $level) {

      $links = [];
      
      $row = [
        $level->id,
        $level->from,
        $level->to,
        $level->points,
      ];

      $links['edit'] = [
        'title' => $this->t('Edit'),
        'url'   => Url::fromRoute('match_point.level.edit', ['id' => $level->id]),
      ];

      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url'   => Url::fromRoute('match_point.level.delete', ['id' => $level->id]),
      ];

      $row[] = [
        'data' => [
          '#type'   => 'operations',
          '#links'  => $links,
        ],
      ];

      $rows[] = $row; 
    }

    $build['match_point_level_table'] = [
      '#type' 	=> 'table',
      '#header' => $header,
      '#rows' 	=> $rows,
      '#empty' 	=> $this->t('No level available.'),
    ];
    
    $build['match_point_level_pager'] = ['#type' => 'pager'];

    return $build;
  }
}
