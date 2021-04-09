<?php

namespace Drupal\quizz\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    $rows   = [];
    $header = [
      [
        'data'  => $this->t('#'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Pseudo'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Ip'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Question'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Given answer'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Good Answer'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
    ];

    $query  = $this->connection->select('quizz_result', 'qr');
    $query->leftJoin('quizz_answer', 'qa', 'qr.answer_id = qa.id');
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
      'quizz_good_answer_id',
      'name'
    ]);
    $query->condition('qr.quizz_id', $quizz_id);
    $query->orderBy('qr.ip');
    $users  = $query->execute();
    
    $prevKey = null;  

    foreach ($users as $user) {
      $key = preg_replace('/\s+/', '', $user->ip.$user->pseudo);

      if (is_null($prevKey) || $prevKey != $key) {
        $prevKey = $key;
        $i = 0;
      }

      if (!isset($rows[$key]['total'])) {
        $rows[$key]['total'] = 0;
      }

      $rows[$key]['total'] += ($user->answer_id != $user->quizz_good_answer_id) ? 0 : 1;
      $i++;
      $rows[$key]['index'] = $i;
      $rows[$key]['value'][] = [
        'data' => [
          $i,
          $user->pseudo,
          $user->ip,
          $user->qq_name,
          $user->name,
          $user->qa2_name,
        ]
      ];
    }

    if (empty($rows)) {
        \Drupal::messenger()->addMessage('No result for this quizz for the moment', 'error');
        return new RedirectResponse(\Drupal\Core\Url::fromRoute('quizz.overview')->toString());
    }

    foreach ($rows as $key => $value) {
      
      $build[$key] = [
        '#type' 	=> 'table',
        '#header' => $header,
        '#rows' 	=> $value['value'],
        '#empty' 	=> $this->t('No result available.'),
        '#footer' => [
          [
            [
              'colspan' => 6,
              'data' => $rows[$key]['total'] . ' bonne(s) réponse(s)',
              'style' => ['text-align:center;', 'font-weight:bold;', 'font-size: 15px;','background:#f5f5f2;', 'border: 1px solid #bfbfba;']
            ]
          ]
        ],
      ];
    }
    
    return $build;
  }
}
