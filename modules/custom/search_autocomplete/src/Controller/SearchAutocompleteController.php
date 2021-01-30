<?php

namespace Drupal\search_autocomplete\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\Xss;

/**
 * Search Autocomplete controller.
 */
class SearchAutocompleteController extends ControllerBase {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorage
   */
  protected $nodeStorage;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\taxonomy\TaxonomyStorage
   */

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\taxonomy\TaxonomyStorage
   */
  protected $taxonomyTermStorage;

  /**
   * nbr items to show.
   */
  const NBR_ITEMS = 5;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->nodeStorage      = $entityTypeManager->getStorage('node');
    $this->taxonomyStorage  = $entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }
  
  /**
   * Save
   * 
   * Request $request request
   */
  public function save(Request $request) {
   
    $config   = \Drupal::config('block.block.searchautocompleteblock');
    $response = new JsonResponse();
    $data     = $request->get('data');
    $results  = [];
    
    $data = Xss::filter($data);

    if (!$data) {
      $response->setData($results);
      return $response;
    }

    if ($config->get('settings.node_enable')) {
      $query = $this->nodeStorage->getQuery()
      ->condition('type', $config->get('settings.node_type'))
      ->condition('title', $data, 'CONTAINS')
      ->condition('status', 1)
      ->groupBy('nid')
      ->range(0, self::NBR_ITEMS);

      $ids    = $query->execute();
      $items  = $ids ? $this->nodeStorage->loadMultiple($ids) : [];
  
      foreach ($items as $item) {
        $results[(string) $this->t($config->get('settings.node_type'))][] = [
          'url'   => $item->toUrl()->toString(),
          'title' => $item->getTitle(),
          'type'  => $config->get('settings.node_type')
        ];
      }
    }

    if ($config->get('settings.taxonomy_enable')) {
      
      $query = $this->taxonomyStorage->getQuery()
        ->condition('vid', $config->get('settings.taxonomy_vocabulary'))
        ->condition('name', $data, 'CONTAINS')
        ->condition('status', 1)
        ->groupBy('tid')
        ->range(0, self::NBR_ITEMS);
     
      $ids    = $query->execute();
      $items  = $ids ? $this->taxonomyStorage->loadMultiple($ids) : [];

      foreach ($items as $item) {
        $results[(string) $this->t($config->get('settings.taxonomy_vocabulary'))][] = [
          'url'   => $item->toUrl()->toString(),
          'title' => $item->getName(),
          'type'  => $config->get('settings.taxonomy_vocabulary')
        ];
      }
    }

    $response->setData($results);
    return $response;
  }
}
