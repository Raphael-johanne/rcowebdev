<?php

namespace Drupal\custom_tokens;

use Drupal\custom_tokens\Manager\FilmManager;

/**
 * Film manager
 */
class Film{
  /**
   * FilmManager.
   *
   * @var FilmManager
   */
  protected $filmManager;

  /**
   * @var FilmManager $filmManager
   */
  public function __construct(FilmManager $filmManager) {
    $this->filmManager  = $filmManager;
  }
  
  /**   
   * Get Formated Films for tokens
   * 
   * int $nbr nbr films
   * 
   * @return string
   */
  public function getFormatedFilms($nbr) {
    /**
     * @TODO create tpl file
     */
    $string = "<ul>";
    
    $datetime = new \DateTime('now');
    $datetime->modify($nbr . " day");
    $datetime->format('Y-m-d H:i:s');
    $films = $this->filmManager->getFilmsByDate($datetime->format('Y-m-d H:i:s'));

    foreach ($films as $film) {
      $string .= sprintf("<li><a href='%s'>%s</a></li>", 
        $film->toUrl()->toString(),
        $film->getTitle()
      );
    }

    $string .= "</ul>";
    return $string;  
  }
}
