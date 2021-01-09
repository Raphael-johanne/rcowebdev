<?php

namespace Drupal\bug_report\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Bug report controller.
 */
class BugReportController extends ControllerBase {

  /**
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Construct
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
   */
  public function __construct(
    RequestStack $requestStack
  ) {
    $this->requestStack   = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }
  
  /**
   * Save
   * 
   * Request $request request
   */
  public function save(Request $request) {
    
    $response = new JsonResponse();
    $message  = $request->get('data');
    $email    = $request->get('email');
    $data     = false;
    
    $data = (!$message) ? false : true;
    
    if ($data === true) {
        $data = $this->sendEmail($message, $email);
    }
    
    $response->setData($data);
    return $response;
  }

  /**
   * sendEmail
   * 
   * string $message message
   */
  private function sendEmail($message, $email) {
    
    $date         = new \DateTime('now');
    $clientIp     = $this->requestStack->getCurrentRequest()->getClientIp();
    $previousUrl  = \Drupal::request()->server->get('HTTP_REFERER');
    $params       = [];  
    $params['message'] = "Bonjour,
    
Un bug a été relevé:
    <ul>
        <li>Date: " . $date->format('H:i:s Y-m-d') . "</li>
        <li>IP: $clientIp</li>
        <li>URL: $previousUrl</li>
        <li>Message: $message</li>
        <li>Email: $email</li>
      </ul>
    ";
    $params['subject'] = 'Bug report';

    $to           = \Drupal::config('system.site')->get('mail');
    $mailManager  = \Drupal::service('plugin.manager.mail');
    $module       = 'bug_report';
    $key          = 'custom_mail_sending_key';
    $langcode     = \Drupal::languageManager()->getCurrentLanguage()->getId();
    
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, true);
    return $result['result']; 
  }
}
