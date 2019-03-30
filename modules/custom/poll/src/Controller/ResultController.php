<?php

namespace Drupal\poll\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Poll\PollManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Result controller.
 */
class ResultController extends ControllerBase {

  /**
   * @var Drupal\Poll\PollManager
   */
  protected $pollManager;

  /**
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Construct
   *
   * @param \Drupal\Poll\PollManager                       $pollManager  Poll Manager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
   */
  public function __construct(
    PollManager $pollManager,
    RequestStack $requestStack
  ) {

    $this->pollManager    = $pollManager;
    $this->requestStack   = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('poll.manager'),
      $container->get('request_stack')
    );
  }
  
  /**
   * Overview
   *
   * @return array
   */
  public function overview() {

    $rows = [];

    $header = [
      [
        'data' => $this->t('ID'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => $this->t('Name'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ]
    ];

    foreach ($this->pollManager->getQuestions() as $pollQuestion) {
      $rows[] = [
        'data' => [
          $pollQuestion->id,
            $this->t($pollQuestion->name),
            $this->l($this->t('View result'), new Url('poll.result.view', ['question_id' => $pollQuestion->id])),
        ]
      ];
    }

    $build['poll_question_table'] = [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $rows,
      '#empty'  => $this->t('No result available.'),
    ];

    return $build;
  }

  /**
   * Save
   *
   * @param int $question_id
   * @param int $answer_id
   */
  public function save(int $question_id, int $answer_id) {
    $currentRequest = $this->requestStack->getCurrentRequest();
    $clientIp       = $currentRequest->getClientIp();

    $errors   = $this->validate($question_id, $answer_id, $clientIp);

    if (!empty($errors)) {
      foreach ($errors as $error) {
        \Drupal::messenger()->addMessage($error, 'error');
      }
    } else {
      $this->pollManager->saveResult($question_id, $answer_id, $clientIp);
      \Drupal::messenger()->addMessage($this->t('Thank you for your vote'), 'success');
    }

    $redirectUrl = ($currentRequest->headers->get('referer'))
    ?: \Drupal\Core\Url::fromRoute('<front>')->toString();

    return new RedirectResponse($redirectUrl);
  }

  /**
   * Validate
   *
   * @param int    $question_id
   * @param int    $answer_id
   * @param string $clientIp

   * @return array
   */
  protected function validate(int $question_id, int $answer_id, $clientIp) {
    $errors = [];

    if ( ! ($this->pollManager->isAvailable($question_id, $answer_id))) {
      var_dump($question_id, $answer_id);die;
      $errors[] = $this->t('This poll is not available.');
    }
    
    if ($this->pollManager->hasPolled($question_id, $clientIp)) {
      $errors[] = $this->t('You have already vote for this poll');
    }

    return $errors;
  }
  /**
   * view
   *
   * @param int $question_id
   */
  public function view(int $question_id) {
    $poll = $this->pollManager->getPoll($question_id);
    $total  = 0;


    foreach ($poll['answers'] as $answer) {
      $total += $answer->nbr;
    }

    return [
      '#theme'  => 'poll_admin_result_template',
      '#poll'   => $poll,
      '#total'  => $total
    ];
  }
}
