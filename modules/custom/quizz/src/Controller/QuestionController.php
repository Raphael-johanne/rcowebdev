<?php

namespace Drupal\quizz\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    $query = $this->connection->select('quizz_question', 'pq')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('pq', [
      'id',
      'name'
    ]);
    
    $quizzQuestions = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();

    foreach ($quizzQuestions as $quizzQuestion) {
      $rows[] = [
        'data' => [
        	$quizzQuestion->id,
          	$this->t($quizzQuestion->name),
          	$this->l($this->t('Edit'), new Url('quizz.question.edit', ['id' => $quizzQuestion->id])),
            $this->l($this->t('Delete'), new Url('quizz.question.delete', ['id' => $quizzQuestion->id]))
        ]
      ];
    }

    $build['quizz_question_table'] = [
      '#type' 	=> 'table',
      '#header' => $header,
      '#rows' 	=> $rows,
      '#empty' 	=> $this->t('No question available.'),
    ];
    $build['quizz_question_pager'] = ['#type' => 'pager'];

    return $build;
  }
}
