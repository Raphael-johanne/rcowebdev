<?php

namespace Drupal\quizz\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\quizz\quizzManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\user\PrivateTempStoreFactory;

/**
 * Quizz controller.
 */
class QuizzController extends ControllerBase {

  /**
   * @var Drupal\quizz\quizzManager
   */
  protected $quizzManager;

  /**
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * 
   */
  protected $tempStore;

  /**
   * Construct
   *
   * @param \Drupal\quizz\quizzManager                     $quizzManager quizzManager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack RequestStack
   */
  public function __construct(
    quizzManager $quizzManager,
    RequestStack $requestStack,
    PrivateTempStoreFactory $temp_store_factory
  ) {

    $this->quizzManager   = $quizzManager;
    $this->requestStack   = $requestStack;
    $this->tempStore      = $temp_store_factory->get('quizz');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('quizz.manager'),
      $container->get('request_stack'),
      $container->get('user.private_tempstore')
    );
  }
  
  /**
   * pseudo
   *
   * @return array
   */
  public function pseudo() {
    $build['quizz_question_pseudo'] = [
      '#theme'  => 'quizz_pseudo_template',
    ];

    return $build;
  }

  /**
   * save Pseudo
   *
   * @return array
   */
  public function savePseudo() {
    $currentRequest = $this->requestStack->getCurrentRequest();
    $pseudo         = $currentRequest->get('quizz')['pseudo'];
    $clientIp       = $currentRequest->getClientIp();

    if (!$currentRequest->get('quizz')['pseudo']
      || $this->quizzManager->hasQuizzed($clientIp, $pseudo)
    ) {
      \Drupal::messenger()->addMessage('Quizz: You alreadty vote', 'error');
      $redirectUrl = \Drupal\Core\Url::fromRoute('<front>')->toString();
    } else {
      $questionId = $this->quizzManager->getFirstQuestionId();
      $this->tempStore->set('quizz.pseudo', $currentRequest->get('quizz')['pseudo']);
      $redirectUrl = \Drupal\Core\Url::fromRoute('quizz.view', ['questionId' => $questionId])->toString();
    }
   
    return new RedirectResponse($redirectUrl);
  }

  /**
   * view
   *
   * @param int $questionId
   */
  public function view(int $questionId) {
    $quizz  = $this->quizzManager->getQuizz($questionId);
    $errors = $this->validate($questionId);
    if (!empty($errors)) {
      foreach ($errors as $error) {
        \Drupal::messenger()->addMessage($error, 'error');
        $redirectUrl = \Drupal\Core\Url::fromRoute('<front>')->toString();
      }
      return new RedirectResponse($redirectUrl);
    }
    return [
      '#theme'  => 'quizz_step_template',
      '#quizz'  => $quizz,
    ];
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
    $pseudo         = $this->tempStore->get('quizz.pseudo');
    $errors         = $this->validate($question_id);

    if (!empty($errors)) {
      foreach ($errors as $error) {
        \Drupal::messenger()->addMessage($error, 'error');
      }
      return new RedirectResponse(\Drupal\Core\Url::fromRoute('<front>')->toString());
    }

    $this->quizzManager->saveResult($question_id, $answer_id, $clientIp, $pseudo);
    $question_id    = $this->getNextId($question_id);

    if (is_null($question_id)) {
      $redirectUrl = \Drupal\Core\Url::fromRoute('quizz.result')->toString();
    } else {
      $redirectUrl = \Drupal\Core\Url::fromRoute('quizz.view', ['questionId' => $question_id])->toString();
    }
      
    return new RedirectResponse($redirectUrl);
  }

  /**
   * result
   *
   */
  public function result() {
    $currentRequest = $this->requestStack->getCurrentRequest();
    $clientIp       = $currentRequest->getClientIp();
    $pseudo         = $this->tempStore->get('quizz.pseudo');
    $results        = $this->quizzManager->getResuls($clientIp, $pseudo);
    $questions      = $this->quizzManager->getQuestions();
    $this->tempStore->set('quizz.pseudo', null);

    $count = 0;
    $total = count($results);
    foreach ($results as &$result) {
      if ( (int) $result->quizz_good_answer_id === (int) $result->qa_id) {
        $count++;
        $result->good_answer = true;
      } else {
        $result->good_answer = false;
      }
    }
    
    if ($total !== count($questions)) {
      \Drupal::messenger()->addMessage('Quizz: Cheater !', 'error');
      return new RedirectResponse(\Drupal\Core\Url::fromRoute('<front>')->toString());
    }

    $build['quizz_qresult'] = [
      '#theme'    => 'quizz_result_template',
      '#results'  => $results,
      '#count'    => $count,
      '#total'    => $total
    ];

    return $build;
  }

  /**
   * Validate
   *
   * @param int    $question_id
   * @param int    $answer_id
   * @param string $clientIp

   * @return array
   */
  protected function validate(int $question_id, int $answer_id = null, $clientIp = null) {
    $errors = [];

    if ( ! $this->tempStore->get('quizz.pseudo')) {
      $errors[] = $this->t('Quizz: Pseudo is required.');
    }
    /*
    if ( ! ($this->quizzManager->isAvailable($question_id, $answer_id))) {
      $errors[] = $this->t('This quizz is not available.');
    }
    */
    //if ($this->quizzManager->hasquizzed($clientIp)) {
    //  $errors[] = $this->t('You have already vote for this quizz');
   // }

    return $errors;
  }

  /**
   * getNextId
   * 
   * int $questionId question id
   */
  private function getNextId(int $questionId) {
    $questionIds  = $this->quizzManager->getQuestionsIds();
    $currentIndex = array_search($questionId, $questionIds);
    return (isset($questionIds[$currentIndex++])) ? $questionIds[$currentIndex++] : null;
  }
}
