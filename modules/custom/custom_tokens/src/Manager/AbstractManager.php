<?php

namespace Drupal\custom_tokens\Manager;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Film manager
 */
Abstract class AbstractManager{
    /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorage
   */
  protected $nodeStorage;

  /**
   * Database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;
 
  /**
   * @var Drupal\Core\Database\Connection $connection
   * @var EntityTypeManagerInterface      $entityTypeManager
   */
  public function __construct(
    Connection $connection, 
    EntityTypeManagerInterface $entityTypeManager
    ) {
    $this->connection   = $connection;
    $this->nodeStorage  = $entityTypeManager->getStorage('node');
  }
}
