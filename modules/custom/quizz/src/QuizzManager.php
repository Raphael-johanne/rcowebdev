<?php

namespace Drupal\quizz;

use Drupal\Core\Database\Connection;

require_once('QuizzManagerInterface.php');
/**
 * quizz manager
 */
class QuizzManager implements quizzManagerInterface {
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
   * getQuizz
   * 
   * @param int $id
   *
   * @return array
   */
  public function getQuizz($id) {
    
    $query = $this->connection->select('quizz_question', 'pq');
    $query->innerJoin('quizz_question_answer', 'pqa', 'pq.id = pqa.question_id'); // join ou innerJoin c'est pareil 
    $query->innerJoin('quizz_answer', 'pa', 'pqa.answer_id = pa.id');
    //$query->leftJoin('quizz_result', 'pr', 'pr.answer_id = pa.id AND pr.question_id = pq.id');
    $query->condition('pq.id', $id);
    $query->fields('pq', ['id', 'name', 'quizz_picture']);
    $query->fields('pa', ['id', 'name']);
    //$query->addExpression('COUNT(pr.id)', 'nbr');
    //$query->groupBy('pa.id, pq.id');

    //var_dump($query->__toString());die;

    $results = $query->execute()
      ->fetchAll();
     
    //var_dump($results);die;

    if (!empty($results)) {
      return [
          'question'    => $results[0]->name,
          'picture'     => $results[0]->quizz_picture,
          'question_id' => $results[0]->id,
          'answers'     => $results
      ];
    }
   
    return null;  
  }

  /**
   * isAvailable
   * 
   * @param int $questionId
   * @param int $answerId
   *
   * @return array
   */
  public function isAvailable($questionId, $answerId) {
    $query = $this->connection->select('quizz_question', 'pq');
    $query->innerJoin('quizz_question_answer', 'pqa', 'pq.id = pqa.question_id'); // join ou innerJoin c'est pareil 
    $query->condition('pq.id', $questionId);
    $query->condition('pqa.answer_id', $answerId);
    $query->range(0, 1);
    $query->fields('pq', ['id']);
    return $query->execute()
      ->fetchField();
  }

  /**
   * hasQuizzed
   * 
   * @param int     $questionId
   * @param varchar $clientIp
   * @param varchar $pseudo
   *
   * @return array
   */
  public function hasQuizzed($clientIp, $pseudo) {
    $query = $this->connection->select('quizz_result', 'pr');
    $query->condition('pr.ip', $clientIp);
    $query->condition('pr.pseudo', $pseudo);
    $query->range(0, 1);
    $query->fields('pr', ['id']);
    return $query->execute()
      ->fetchField();
  }

  /**
   * getQuestions
   * 
   * @return array
   */
  public function getQuestions() {
    $query = $this->connection->select('quizz_question', 'qq');
    $query->fields('qq', ['id', 'name']);
    return $query->execute()
      ->fetchAll();
  }

  /**
   * saveResult
   * 
   * @param int     $questionId
   * @param int     $answerId
   * @param varchar $clientIp
   * @param varchar $pseudo
   *
   * @return void
   */
  public function saveResult($questionId, $answerId, $clientIp, $pseudo) {
    $id = $this->connection->insert('quizz_result')
      ->fields([
        'question_id' => $questionId,
        'answer_id'   => $answerId,
        'ip'          => $clientIp,
        'pseudo'      => $pseudo,
      ])
      ->execute();
  }

  /**
   * getFirstQuestionId  
   * 
   * @return array
   */
  public function getFirstQuestionId() {
    $query = $this->connection->select('quizz_question', 'qq');
    $query->range(0, 1);
    $query->orderBy('id', 'ASC');
    $query->fields('qq', ['id']);
    return $query->execute()
      ->fetchField();
  }


  /**
   * getQuestionsIds  
   * 
   * @return array
   */
  public function getQuestionsIds() {
    $query = $this->connection->select('quizz_question', 'qq');
    $query->orderBy('id', 'ASC');
    $query->fields('qq', ['id']);
    return $query->execute()
      ->fetchCol();
  }

    /**
   * getResuls
   * 
   * @param string $clientIp
   * @param string $pseudo
   *
   * @return array
   */
  public function getResuls($clientIp, $pseudo) {
    $query = $this->connection->select('quizz_result', 'qr');
    $query->innerJoin('quizz_question', 'qq', 'qq.id = qr.question_id'); // join ou innerJoin c'est pareil 
    $query->innerJoin('quizz_answer', 'qa', 'qa.id = qr.answer_id'); // join ou innerJoin c'est pareil 
    $query->innerJoin('quizz_answer', 'qa2', 'qa2.id = qq.quizz_good_answer_id'); // join ou innerJoin c'est pareil
    $query->condition('ip', $clientIp);
    $query->condition('pseudo', $pseudo);
    $query->fields('qq', ['id', 'name', 'quizz_good_answer_id']);
    $query->fields('qa', ['id', 'name']);
    $query->fields('qa2', ['id', 'name']);
    // var_dump($query->__toString());die;
    return $query->execute()
      ->fetchAll();
  }

}
