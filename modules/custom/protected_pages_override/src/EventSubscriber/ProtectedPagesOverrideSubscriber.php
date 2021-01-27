<?php

namespace Drupal\protected_pages_override\EventSubscriber;

use Drupal\protected_pages\EventSubscriber\ProtectedPagesSubscriber;

/**
 * Redirects user to protected page login screen.
 */
class ProtectedPagesOverrideSubscriber extends ProtectedPagesSubscriber {

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

    /**
     * RACOL MODIFICATION START
     */
    if ($pid === false) {
      return;
    }
    
    if (!isset($_SESSION['_protected_page']['global_id'])) {
      $_SESSION['_protected_page']['global_id'] = $pid;
    }

    $pid = $_SESSION['_protected_page']['global_id'];
    /**
     * RACOL MODIFICATION END
     */

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
