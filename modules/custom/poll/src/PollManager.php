<?php

namespace Drupal\Poll;

use Drupal\Core\Database\Connection;

/**
 * Poll manager
 */
class PollManager implements PollManagerInterface {
  /**
   * Database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;
 
  /**
   * @var Drupal\Core\Database\Connection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }
  
  /**
   * @param int $id
   *
   * @return array
   */
  public function getPoll($id) {
    $query = $this->connection->select('poll_question', 'pq');
    $query->innerJoin('poll_question_answer', 'pqa', 'pq.id = pqa.question_id'); // join ou innerJoin c'est pareil 
    $query->innerJoin('poll_answer', 'pa', 'pqa.answer_id = pa.id');
    $query->leftJoin('poll_result', 'pr', 'pr.answer_id = pa.id AND pr.question_id = pq.id');
    $query->condition('pq.id', $id);
    $query->fields('pq', ['id', 'name']);
    $query->fields('pa', ['id', 'name']);
    $query->addExpression('COUNT(pr.id)', 'nbr');
    $query->groupBy('pa.id, pq.id');

    $results = $query->execute()
      ->fetchAll();

    if (!empty($results)) {
      return [
          'question'    => $results[0]->name,
          'question_id' => $results[0]->id,
          'answers'     => $results
      ];
    }

    return null;  
  }

  /**
   * @param int $questionId
   * @param int $answerId
   *
   * @return array
   */
  public function isAvailable($questionId, $answerId) {
    $query = $this->connection->select('poll_question', 'pq');
    $query->innerJoin('poll_question_answer', 'pqa', 'pq.id = pqa.question_id'); // join ou innerJoin c'est pareil 
    $query->condition('pq.id', $questionId);
    $query->condition('pqa.answer_id', $answerId);
    $query->range(0, 1);
    $query->fields('pq', ['id']);
    return $query->execute()
      ->fetchField();
  }

  /**
   * @param int     $questionId
   * @param varchar $clientIp
   *
   * @return array
   */
  public function hasPolled($questionId, $clientIp) {
    $query = $this->connection->select('poll_result', 'pr');
    $query->condition('pr.question_id', $questionId);
    $query->condition('pr.ip', $clientIp);
    $query->range(0, 1);
    $query->fields('pr', ['id']);
    return $query->execute()
      ->fetchField();
  }

  /**
   * @return array
   */
  public function getQuestions() {
    $query = $this->connection->select('poll_question', 'pq');
    $query->fields('pq', ['id', 'name']);
    return $query->execute()
      ->fetchAll();
  }

  /**
   * @param int     $questionId
   * @param int     $answerId
   * @param varchar $clientIp
   *
   * @return void
   */
  public function saveResult($questionId, $answerId, $clientIp) {
    $id = $this->connection->insert('poll_result')
      ->fields([
        'question_id' => $questionId,
        'answer_id'   => $answerId,
        'ip'          => $clientIp
      ])
      ->execute();
  }
}
