<?php

namespace Drupal\Poll;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface PollManagerInterface extends PluginInspectionInterface {
  /**
   * Get poll.
   *
   * @return array
   */
  public function getPoll();

   /**
   * @param int $questionId
   * @param int $answerId
   *
   * @return array
   */
  public function isAvailable($questionId, $answerId);

  /**
   * @param int     $questionId
   * @param varchar $clientIp
   *
   * @return array
   */
  public function hasPolled($questionId, $clientIp);

  /**
   * @return array
   */
  public function getQuestions();

  /**
   * @param int     $questionId
   * @param int     $answerId
   * @param varchar $clientIp
   *
   * @return void
   */
  public function saveResult($questionId, $answerId, $clientIp);
}
