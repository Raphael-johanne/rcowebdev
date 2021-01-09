<?php

namespace Drupal\quizz\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\quizz\quizzManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Result controller.
 */
class ResultController extends ControllerBase {

  /**
   * @var Drupal\quizz\quizzManager
   */
  protected $quizzManager;

  /**
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Construct
   *
   * @param \Drupal\quizz\quizzManager                       $quizzManager  quizz Manager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
   */
  public function __construct(
    quizzManager $quizzManager,
    RequestStack $requestStack
  ) {

    $this->quizzManager    = $quizzManager;
    $this->requestStack   = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('quizz.manager'),
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

    foreach ($this->quizzManager->getQuestions() as $quizzQuestion) {
      $rows[] = [
        'data' => [
          $quizzQuestion->id,
            $this->t($quizzQuestion->name),
            $this->l($this->t('View result'), new Url('quizz.result.view', ['question_id' => $quizzQuestion->id])),
        ]
      ];
    }

    $build['quizz_question_table'] = [
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

    $errors         = $this->validate($question_id, $answer_id, $clientIp);

    if (!empty($errors)) {
      foreach ($errors as $error) {
        \Drupal::messenger()->addMessage($error, 'error');
      }
    } else {
      $this->quizzManager->saveResult($question_id, $answer_id, $clientIp);
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

    if ( ! ($this->quizzManager->isAvailable($question_id, $answer_id))) {
      $errors[] = $this->t('This quizz is not available.');
    }
    
    if ($this->quizzManager->hasquizzed($question_id, $clientIp)) {
      $errors[] = $this->t('You have already vote for this quizz');
    }

    return $errors;
  }
  /**
   * view
   *
   * @param int $question_id
   */
  public function view(int $question_id) {
    $quizz   = $this->quizzManager->getquizz($question_id);
    $total  = 0;

    foreach ($quizz['answers'] as $answer) {
      $total += $answer->nbr;
    }

    return [
      '#theme'  => 'quizz_admin_result_template',
      '#quizz'   => $quizz,
      '#total'  => $total
    ];
  }
}
