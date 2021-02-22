<?php

namespace Drupal\custom_tokens;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Film manager
 */
class FilmManager{
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
  /**   
   * Get Formated Films for tokens
   * 
   * @return string
   */
  public function getFormatedFilms() {
    /**
     * @TODO create tpl file
     */
    $string = "<ul>";

    $datetime = new \DateTime('now');
    $datetime->modify('-7 day');
    $datetime->format('Y-m-d H:i:s');
    $films = $this->getFilmsByDate($datetime->format('Y-m-d H:i:s'));
    
    foreach ($films as $film) {
      $string .= sprintf("<li><a href='%s'>%s</a></li>", 
        $film->toUrl()->toString(),
        $film->getTitle()
      );
    }

    $string .= "</ul>";

    return $string;  
  }

  /**
   * Get films
   * 
   * @param string $date
   * @param string $operator
   * 
   * @return array
   */
  public function getFilmsByDate($date, $operator = ">") {
    $query = $this->nodeStorage->getQuery()
    ->condition('type', 'film')
    ->condition('field_shared_date', $date, $operator)
    ->condition('status', 1)
    ->groupBy('nid');

    $ids  = $query->execute();
    return $ids ? $this->nodeStorage->loadMultiple($ids) : [];
  }
}
