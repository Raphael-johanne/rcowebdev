<?php

namespace Drupal\quizz;

interface QuizzManagerInterface {
  /** 
   * Get quizz.
   *
   * @param int $id
   *
   * @return array
   */
  public function getquizz($id);

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
   *
   * @return array
   */
  public function hasquizzed($clientIp, $pseudo);

  /**
   * @return array
   */
  public function getQuestions();

  /**
   * @param int     $questionId
   * @param int     $answerId
   * @param varchar $clientIp
   * @param varchar $pseudo
   *
   * @return void
   */
  public function saveResult($questionId, $answerId, $clientIp, $pseudo);
}
