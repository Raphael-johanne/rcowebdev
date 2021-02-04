<?php

namespace Drupal\match_point;

use Drupal\Core\Database\Connection;

require_once('MatchPointManagerInterface.php');
/**
 * Poll manager
 */
class MatchPointManager implements MatchPointManagerInterface {
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
   * get Users
   * 
   * @return array
   */
  public function getUsers() {
    $query = $this->connection->select('match_point_user', 'mpu');
    $query->fields('mpu', ['id', 'name', 'picture', 'points']);
    $query->orderBy('points', 'DESC');
    $query->range(0, 3);
    return $query->execute()
      ->fetchAll();
  }

  /**
   * get total points
   * 
   * @return int|null
   */
  public function getTotalPointsInformations() {
    $query = $this->connection->select('match_point_user', 'mpu');
    $query->addExpression('SUM(points)', 'total');
    $query->addExpression('COUNT(id)', 'nb');
    return $query->execute()
    ->fetchAll()[0];
  }

  /**
   * get user by id
   * 
   * @return mixed
   */
  public function getUserById($userId) {
    return $this->connection->select('match_point_user')
        ->fields('match_point_user', 
            [
                'name',
                'picture',
                'points'
            ]
        )
        ->condition('id', $userId, "=")
        ->execute()
        ->fetchAll()[0];
  }

  /**
   * get level by id
   * 
   * @return mixed
   */
  public function getLevelById($id) {
    return $this->connection->select('match_point_level')
        ->fields('match_point_level', 
            [
                'from',
                'to',
                'points'
            ]
        )
        ->condition('id', $id, "=")
        ->execute()
        ->fetchAll()[0];
  }

  /**
   * get earn points 
   * 
   * @return int
   */
  public function getEarnPointsByPoints($points = 0) {
    return $this->connection->select('match_point_level', 'mpl')
        ->fields('mpl', 
            [
                'points'
            ]
        )
        ->condition('mpl.from',(int) $points, "<")
        ->condition('mpl.to', (int) $points, ">=")
        ->execute()
        ->fetchAll()[0]
        ->points;
  }

}
