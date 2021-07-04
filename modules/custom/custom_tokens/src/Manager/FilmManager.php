<?php

namespace Drupal\custom_tokens\Manager;

use Drupal\custom_tokens\Manager\AbstractManager;

/**
 * Film manager
 */
class FilmManager extends AbstractManager{

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
