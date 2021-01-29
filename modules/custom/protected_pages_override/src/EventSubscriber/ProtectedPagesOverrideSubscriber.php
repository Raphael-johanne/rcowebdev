<?php

namespace Drupal\protected_pages_override\EventSubscriber;

use Drupal\protected_pages\EventSubscriber\ProtectedPagesSubscriber;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\user\Entity\User;

/**
 * Redirects user to protected page login screen.
 */
class ProtectedPagesOverrideSubscriber extends ProtectedPagesSubscriber {

  /**
   * Caching protected pages
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   * 
   * @return void
   */
  public function cachingProtectedPages(FilterResponseEvent $event) {
     
    $config = \Drupal::config('protected_pages_override.settings');

    if (!$config->get('enable')) {
      return;
    }

    $headers          = apache_request_headers();
    $headerUserAgent  = (isset($headers['User-Agent'])) ? $headers['User-Agent'] : null;

    /** 
     * @TODO URGENT SECURITY RISK:  add Secret-Key in headers
     * $headerSecretKey   = (isset($headers['Secret-Key'])) ? $headers['Secret-Key'] : null;
     * $secretKey         = $config->get('secret_key');
     */
    $pathSpider           = $config->get('spider_path');
    $authorizedUserAgent  = $config->get('authorized_user_agent');
    $uri                  = $this->requestStack->getCurrentRequest()->getUri();

    if ($headerUserAgent === $authorizedUserAgent
    // @TODO URGENT SECURITY RISK:  add Secret-Key in headers
    // && $headerSecretKey === $secretKey 
    && strrpos($uri, $pathSpider) !== false
    ) {
      $user = User::load($this->currentUser->id());
      $user->addRole($config->get('spider_role_name'));
      $user->save();
      $this->currentUser->setAccount($user);
      \Drupal::logger('protected_pages_override')->notice("PROTECTED PAGES CACHED: " . $uri);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['cachingProtectedPages'];
    $events[KernelEvents::RESPONSE][] = ['checkProtectedPage'];
    return $events;
  }

  /**
   * Returns protected page id.
   *
   * @param string $current_path
   *   Current path alias.
   * @param string $normal_path
   *   Current normal path.
   *
   * @return int
   *   The protected page id.
   */
  public function protectedPagesIsPageLocked(string $current_path, string $normal_path) {
    
    $fields = ['pid'];
    $conditions = [];
    $conditions['or'][] = [
      'field' => 'path',
      'value' => $normal_path,
      'operator' => '=',
    ];
    $conditions['or'][] = [
      'field' => 'path',
      'value' => $current_path,
      'operator' => '=',
    ];
    $pid = $this->protectedPagesStorage->loadProtectedPage($fields, $conditions, TRUE);

    if ($pid === false) {
      return;
    }
    
    if (!isset($_SESSION['_protected_page']['global_id'])) {
      $_SESSION['_protected_page']['global_id'] = $pid;
    }

    $pid = $_SESSION['_protected_page']['global_id'];

    if (isset($_SESSION['_protected_page']['passwords'][$pid]['expire_time'])) {
      if (time() >= $_SESSION['_protected_page']['passwords'][$pid]['expire_time']) {
        unset($_SESSION['_protected_page']['passwords'][$pid]['request_time']);
        unset($_SESSION['_protected_page']['passwords'][$pid]['expire_time']);
      }
    }
    if (isset($_SESSION['_protected_page']['passwords'][$pid]['request_time'])) {
      return FALSE;
    }
    return $pid;
  }
}
