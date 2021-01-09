<?php
/**
 * @file
 * Contains \Drupal\forms_overwrite\Routing\RouteSubscriber.
 */

namespace Drupal\forms_overwrite\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

    /**
     * {@inheritdoc}
     */
    protected function alterRoutes(RouteCollection $collection) {
        $route = $collection->get('search.view_node_search') ?: $collection->get('search.view_content_exclude_');
        if ($route) {
            $route->setDefault('_controller', '\Drupal\forms_overwrite\Controller\ResultSearchController::view');
        }
    }
}
