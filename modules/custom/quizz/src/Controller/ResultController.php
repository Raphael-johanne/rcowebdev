<?php

namespace Drupal\quizz\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Question controller.
 */
class ResultController extends ControllerBase {

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
  public function overview($quizz_id) {

    $header = [
      [
        'data' => $this->t('Pseudo'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => $this->t('Given answer'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => $this->t('Good Answer'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    $query = $this->connection->select('quizz_result', 'qr');
    $query->innerJoin('quizz_answer', 'qa', 'qr.answer_id = qa.id');
    $query->innerJoin('quizz_question', 'qq', 'qr.question_id = qq.id');
    $query->innerJoin('quizz_answer', 'qa2', 'qq.quizz_good_answer_id = qa2.id');
    $query->fields('qr', [
      'ip',
      'pseudo',
      'answer_id'
    ]);
    $query->fields('qa', [
      'name'
    ]);
    $query->fields('qa2', [
      'name'
    ]);
    $query->fields('qq', [
      'quizz_good_answer_id'
    ]);
    $query->condition('qr.quizz_id', $quizz_id);

    $users  = $query->execute();

    foreach ($users as $user) {
      if (!isset($rows[$user->ip.$user->pseudo]['total'])) {
        $rows[$user->ip.$user->pseudo]['total'] = 0;
      }

      $rows[$user->ip.$user->pseudo]['total'] += ($user->answer_id != $user->quizz_good_answer_id) ? 0 : 1;

      $rows[$user->ip.$user->pseudo]['value'][] = [
        'data' => [
          $user->pseudo,
          $user->name,
          $user->qa2_name,
        ]
      ];
    }

    foreach ($rows as $key => $value) {
      $build[$key] = [
        '#type' 	=> 'table',
        '#header' => $header,
        '#rows' 	=> $value['value'],
        '#empty' 	=> $this->t('No result available.'),
      ];
    }

    return $build;
  }
}
