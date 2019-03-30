<?php

namespace Drupal\poll\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Question controller.
 */
class QuestionController extends ControllerBase {

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
   *
   * @return array
   */
  public function overview() {

    $rows = [];

    $header = [
      [
        'data' => $this->t('ID'),
        'field' => 'pq.id',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => $this->t('Name'),
        'field' => 'pq.name',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    $query = $this->connection->select('poll_question', 'pq')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('pq', [
      'id',
      'name'
    ]);
    
    $pollQuestions = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();

    foreach ($pollQuestions as $pollQuestion) {
      $rows[] = [
        'data' => [
        	$pollQuestion->id,
          	$this->t($pollQuestion->name),
          	$this->l($this->t('Edit'), new Url('poll.question.edit', ['id' => $pollQuestion->id])),
            $this->l($this->t('Delete'), new Url('poll.question.delete', ['id' => $pollQuestion->id]))
        ]
      ];
    }

    $build['poll_question_table'] = [
      '#type' 	=> 'table',
      '#header' => $header,
      '#rows' 	=> $rows,
      '#empty' 	=> $this->t('No question available.'),
    ];
    $build['poll_question_pager'] = ['#type' => 'pager'];

    return $build;
  }
}
