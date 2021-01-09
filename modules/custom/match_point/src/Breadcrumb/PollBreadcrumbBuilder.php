<?php

namespace Drupal\poll\Breadcrumb;
 
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link ;
 
class PollBreadcrumbBuilder implements BreadcrumbBuilderInterface {

	/**
	* {@inheritdoc}
	*/
	public function applies(RouteMatchInterface $attributes) {
		return strrpos($attributes->getRouteName(), 'poll') === 0;
	}
 
	/**
	* {@inheritdoc}
	*/
	public function build(RouteMatchInterface $route_match) {

		$breadcrumb = new Breadcrumb();
		$breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
		$breadcrumb->addLink(Link::createFromRoute('Poll', 'poll.question.overview'));

		switch ( $route_match->getRouteName() ) {
			case 'poll.answer.edit':
			case 'poll.answer.delete':
				$breadcrumb->addLink(Link::createFromRoute('Poll Answers', 'poll.answer.overview'));
			break;
			case 'poll.result.view':
				$breadcrumb->addLink(Link::createFromRoute('Poll Results', 'poll.result.overview'));
			break;
		}

		$breadcrumb->addCacheContexts(['route']);

		return $breadcrumb;
	}
}
