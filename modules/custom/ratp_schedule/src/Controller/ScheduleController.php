<?php

namespace Drupal\ratp_schedule\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Database\Connection;
/**
 * Bus controller.
 */
class ScheduleController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Construct
   *
   * @param \Drupal\Core\Database\Connection $databaseConnection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
  * overview
   */
  public function overview() {

    $rows = [];

    $header = [
      [
        'data'  => $this->t('ID'),
        'field' => 'pa.ratp_schedule_id',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Name'),
        'field' => 'pa.ratp_schedule_name',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    $query = $this->connection->select('ratp_schedule', 'pa')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('pa', [
      'ratp_schedule_id',
      'ratp_schedule_name'
    ]);
    
    $items = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();

    foreach ($items as $item) {
      $rows[] = [
        'data' => [
        	  $item->ratp_schedule_id,
          	$this->t($item->ratp_schedule_name),
          	$this->l($this->t('Edit'), new Url('ratp_schedule.schedule.edit', ['id' => $item->ratp_schedule_id])),
            $this->l($this->t('Delete'), new Url('ratp_schedule.schedule.delete', ['id' => $item->ratp_schedule_id]))
        ]
      ];
    }

    $build['ratp_schedule_table'] = [
      '#type' 	=> 'table',
      '#header' => $header,
      '#rows' 	=> $rows,
      '#empty' 	=> $this->t('No schedule available.'),
    ];
    $build['ratp_schedule_pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
  * selection
   */
  public function selection() {

    $build['ratp_schedule_selection'] = [
      '#theme' 	    => 'ratp_schedule_selection_template',
      '#empty' 	    => $this->t('No schedule available.'),
      '#schedules'  => $this->getSchedules(),
      '#cache'      => ['max-age' => 0]
    ];

    return $build;
  }

  /**
   * 
   */
  protected function getSchedules() {
    return $this->connection->select('ratp_schedule')
            ->fields('ratp_schedule', [
              'ratp_schedule_id',
              'ratp_schedule_name', 
              'ratp_schedule_type',
              'ratp_schedule_station_departure',
            ])
            ->execute()
            ->fetchAll();
  }
}
