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
   * get Question By Id
   * 
   * @param int $id
   * @param int $quizzId
   *
   * @return array
   */
  public function getQuestionById($id, $quizzId) {
    
    $query = $this->connection->select('quizz_question', 'qq');
    $query->innerJoin('quizz_question_answer', 'qqa', 'qq.id = qqa.question_id');
    $query->innerJoin('quizz_answer', 'qa', 'qqa.answer_id = qa.id');
    $query->innerJoin('quizz_quizz_question', 'qqq', 'qqq.question_id = qq.id'); 
    $query->innerJoin('quizz', 'q', 'qqq.quizz_id = q.id'); 
    $query->condition('qq.id', $id);
    $query->condition('q.id', $quizzId);
    $query->fields('qq', ['id', 'name', 'quizz_picture']);
    $query->fields('qa', ['id', 'name']);
    $results = $query->execute()
      ->fetchAll();
     
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
    $query->innerJoin('quizz_question_answer', 'pqa', 'pq.id = pqa.question_id');
    $query->condition('pq.id', $questionId);
    $query->condition('pqa.answer_id', $answerId);
    $query->range(0, 1);
    $query->fields('pq', ['id']);
    return $query->execute()
      ->fetchField();
  }

  /**
   * has Quizzed
   * 
   * @param varchar $clientIp
   * @param varchar $pseudo
   * @param int     $quizzId
   *
   * @return array
   */
  public function hasQuizzed($clientIp, $pseudo, $quizzId) {
    $query = $this->connection->select('quizz_result', 'pr');
    $query->condition('pr.ip', $clientIp);
    $query->condition('pr.quizz_id', $quizzId);
    $query->condition('pr.pseudo', $pseudo);
    $query->range(0, 1);
    $query->fields('pr', ['id']);
    return $query->execute()
      ->fetchField();
  }

  /**
   * get Questions
   * 
   * @return array
   * @param int     $quizzId
   * 
   */
  public function getQuestions($quizzId = null) {
    $d = [];

    $query = $this->connection->select('quizz_question', 'qq')
      ->fields('qq', ['id', 'name']);

    if (!is_null($quizzId)) {
      $query->innerJoin('quizz_quizz_question', 'qqq', 'qqq.question_id = qq.id'); 
      $query->innerJoin('quizz', 'q', 'qqq.quizz_id = q.id'); 
      $query->condition('q.id', $quizzId);
    }  
    
    $query =  $query->execute()
    ->fetchAll();
      
    foreach ($query as $question) {
        $d[$question->id] = $question->name;
    }

    return $d; 
  }

  /**
   * saveResult
   * 
   * @param int     $questionId
   * @param int     $answerId
   * @param varchar $clientIp
   * @param varchar $pseudo
   * @param int     $quizzId
   *
   * @return void
   */
  public function saveResult($questionId, $answerId, $clientIp, $pseudo, $quizzId) {
    $id = $this->connection->insert('quizz_result')
      ->fields([
        'question_id' => $questionId,
        'answer_id'   => $answerId,
        'ip'          => $clientIp,
        'pseudo'      => $pseudo,
        'quizz_id'      => $quizzId,
      ])
      ->execute();
  }

  /**
   * get Questions Ids  
   * 
   * @param int $quizzId
   * 
   * @return array
   */
  public function getQuestionsIds($quizzId) {
    $query = $this->connection->select('quizz_question', 'qq');
    $query->innerJoin('quizz_quizz_question', 'qqq', 'qqq.question_id = qq.id'); 
    $query->innerJoin('quizz', 'q', 'qqq.quizz_id = q.id'); 
    $query->orderBy('id', 'ASC');
    $query->fields('qq', ['id']);
    $query->condition('q.id', $quizzId);
    return $query->execute()
      ->fetchCol();
  }

    /**
   * getResuls
   * 
   * @param string $clientIp
   * @param string $pseudo
   * @param int    $quizzId
   * 
   * @return array
   */
  public function getResuls($clientIp, $pseudo, $quizzId) {
    $query = $this->connection->select('quizz_result', 'qr');
    $query->innerJoin('quizz_question', 'qq', 'qq.id = qr.question_id');
    $query->innerJoin('quizz_answer', 'qa', 'qa.id = qr.answer_id');
    $query->innerJoin('quizz_answer', 'qa2', 'qa2.id = qq.quizz_good_answer_id');
    $query->innerJoin('quizz_quizz_question', 'qqq', 'qqq.question_id = qq.id'); 
    $query->innerJoin('quizz', 'q', 'qqq.quizz_id = q.id'); 
    $query->condition('ip', $clientIp);
    $query->condition('pseudo', $pseudo);
    $query->condition('q.id', $quizzId);
    $query->fields('qq', ['id', 'name', 'quizz_good_answer_id']);
    $query->fields('qa', ['id', 'name']);
    $query->fields('qa2', ['id', 'name']);
    // var_dump($query->__toString());die;
    return $query->execute()
      ->fetchAll();
  }

  /**
   * get quizzs
   * 
   * @return array
   */
  public function getQuizzs() {
    $query = $this->connection->select('quizz', 'q');
    $query->fields('q', ['id', 'name', 'available']);
    return $query->execute()
      ->fetchAll();
  }

  /**
   *
   * get Quizz by id
   * 
   * @return mixed
   */
  public function getQuizzById(int $id) {

    return $this->connection->select('quizz')
        ->fields('quizz', ['name', 'available'])
        ->condition('id', $id, "=")
        ->execute()
        ->fetchAll()[0];
  }

  /**
   *
   * getAvailableQuizz
   * 
   * @return mixed
   */
  public function getAvailableQuizz() {

    return $this->connection->select('quizz')
        ->fields('quizz', ['name', 'id'])
        ->condition('available', 1, "=")
        ->execute()
        ->fetchAll()[0];
  }
  
  /**
   * Get selected questions by quizz id
   *
   * @return array
   */
  public function getSelectedQuestionsByQuizzId(int $qizzId) {
      $d = [];

      $relations = $this->connection->select('quizz_quizz_question')
          ->fields('quizz_quizz_question', ['question_id'])
          ->condition('quizz_id', $qizzId, "=")
          ->execute()
          ->fetchAll();
        
      foreach ($relations as $relation) {
          $d[] = $relation->question_id;
      }

      return $d;
    }
}