<?php

namespace Drupal\forms_overwrite\Controller;

use Drupal\search\SearchPageInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search\Controller\SearchController;

/**
 * Route controller for search.
 */
class ResultSearchController extends SearchController {

    /**
     * {@inheritdoc}
     */
    public function view(Request $request, SearchPageInterface $entity) {
        $build = parent::view( $request, $entity);
        unset($build['search_form']['help_link']);
        return $build;
    }
}
