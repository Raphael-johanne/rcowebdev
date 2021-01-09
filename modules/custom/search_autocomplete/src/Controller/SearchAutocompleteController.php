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
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
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
    
    $response   = new JsonResponse();
    $data       = $request->get('data');
    $results    = [];
    
    $data = Xss::filter($data);

    if (!$data) {
      $response->setData($results);
      return $response;
    }

    $query = $this->nodeStorage->getQuery()
      ->condition('type', 'film') // @TODO rendre paramétrable
      ->condition('title', $data, 'CONTAINS')
      ->condition('status', 1)
      ->groupBy('nid')
      ->range(0, 10);

    $ids = $query->execute();
    $nodes = $ids ? $this->nodeStorage->loadMultiple($ids) : [];

    foreach ($nodes as $node) {
      $results[] = [
        'url'   => $node->toUrl()->toString(),
        'title' => $node->getTitle(),
      ];
    }
    
    $response->setData($results);
    return $response;
  }
}
