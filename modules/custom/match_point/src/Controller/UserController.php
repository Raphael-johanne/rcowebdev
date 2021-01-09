<?php

namespace Drupal\match_point\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Answer controller.
 */
class UserController extends ControllerBase {

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
        'data'  => $this->t('Name'),
        'field' => 'mpu.name',
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
      $rows[] = [
        'data' => [
        	  $user->id,
          	$this->t($user->name),
            $user->points,
          	$this->l($this->t('Edit'), new Url('match_point.user.edit', ['id' => $user->id])),
            $this->l($this->t('Delete'), new Url('match_point.user.delete', ['id' => $user->id]))
        ]
      ];
    }

    $build['match_point_user_table'] = [
      '#type' 	=> 'table',
      '#header' => $header,
      '#rows' 	=> $rows,
      '#empty' 	=> $this->t('No user available.'),
    ];
    $build['match_point_user_pager'] = ['#type' => 'pager'];

    return $build;
  }
}
