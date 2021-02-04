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
        'field' => 'mpu.id',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('From'),
        'field' => 'mpu.name',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('To'),
        'field' => 'mpu.points',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Points'),
        'field' => 'mpu.points',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    $query = $this->connection->select('match_point_user', 'mpu')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('mpu', [
      'id',
      'name',
      'points'
    ]);
    
    $users = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();

    foreach ($users as $user) {

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
