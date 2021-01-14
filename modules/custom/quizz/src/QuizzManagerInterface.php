<?php

namespace Drupal\quizz;

interface QuizzManagerInterface {
  /** 
   * Get question
   *
   * @param int $id
   * @param int $quizzId
   *
   * @return array
   */
  public function getQuestionById($id, $quizzId);

   /**
   * @param int $questionId
   * @param int $answerId
   *
   * @return array
   */
  public function isAvailable($questionId, $answerId);

  /**
   * @param varchar $clientIp
   * @param varchar $pseudo
   * @param int     $quizzId
   *
   * @return array
   */
  public function hasquizzed($clientIp, $pseudo, $quizzId);

  /**
   * @return array
   */
  public function getQuestions();

  /**
   * @param int     $questionId
   * @param int     $answerId
   * @param varchar $clientIp
   * @param varchar $pseudo
   * @param int     $quizzId
   *
   * @return void
   */
  public function saveResult($questionId, $answerId, $clientIp, $pseudo, $quizzId);
}
