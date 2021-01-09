<?php

namespace Drupal\quizz\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Answer controller.
 */
class AnswerController extends ControllerBase {

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
        'field' => 'pa.id',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Name'),
        'field' => 'pa.name',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    $query = $this->connection->select('quizz_answer', 'pa')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('pa', [
      'id',
      'name'
    ]);
    
    $quizzAnwers = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();

    foreach ($quizzAnwers as $quizzAnswer) {
      $rows[] = [
        'data' => [
        	$quizzAnswer->id,
          	$this->t($quizzAnswer->name),
          	$this->l($this->t('Edit'), new Url('quizz.answer.edit', ['id' => $quizzAnswer->id])),
            $this->l($this->t('Delete'), new Url('quizz.answer.delete', ['id' => $quizzAnswer->id]))
        ]
      ];
    }

    $build['quizz_answer_table'] = [
      '#type' 	=> 'table',
      '#header' => $header,
      '#rows' 	=> $rows,
      '#empty' 	=> $this->t('No answer available.'),
    ];
    $build['quizz_answer_pager'] = ['#type' => 'pager'];

    return $build;
  }
}
