<?php

namespace Drupal\match_point\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Winner controller.
 */
class WinnerController extends ControllerBase {

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
        'field' => 'mpw.id',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Name'),
        'field' => 'mpu.name',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('From'),
        'field' => 'mpw.from',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('To'),
        'field' => 'mpw.to',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Points'),
        'field' => 'mpw.points',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Available'),
        'field' => 'mpw.available',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    $query = $this->connection->select('match_point_winner', 'mpw')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('mpw', ['id', 'points', 'from', 'to', 'available']);
    $query->fields('mpu', ['name']);
    $query->innerJoin('match_point_user', 'mpu', 'mpw.user_id = mpu.id'); 
    $users = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();

    foreach ($users as $user) {

      $links = [];
      
      $row = [
        $user->id,
        $user->name,
        $user->from,
        $user->to,
        $user->points,
        $user->available,
      ];

      $links['edit'] = [
        'title' => $this->t('Edit'),
        'url'   => Url::fromRoute('match_point.winner.edit', ['id' => $user->id]),
      ];

      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url'   => Url::fromRoute('match_point.winner.delete', ['id' => $user->id]),
      ];

      $row[] = [
        'data' => [
          '#type'   => 'operations',
          '#links'  => $links,
        ],
      ];

      $rows[] = $row; 
    }

    $build['match_point_winner_table'] = [
      '#type' 	=> 'table',
      '#header' => $header,
      '#rows' 	=> $rows,
      '#empty' 	=> $this->t('No winner available.'),
    ];
    
    $build['match_point_winner_pager'] = ['#type' => 'pager'];

    return $build;
  }
}
