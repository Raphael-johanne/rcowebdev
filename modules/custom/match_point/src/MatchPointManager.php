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


}
