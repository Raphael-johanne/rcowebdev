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
   * @var Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * @var \stdObject
   */
  private $currentQuizzId;

  /**
   * Construct
   *
   * @param \Drupal\quizz\quizzManager                     $quizzManager      Quizz Manager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack      Request Stack
   * @param \Drupal\user\PrivateTempStoreFactory           $tempStoreFactory  temp Store Factory
   */
  public function __construct(
    quizzManager $quizzManager,
    RequestStack $requestStack,
    PrivateTempStoreFactory $tempStoreFactory
  ) {

    $this->quizzManager     = $quizzManager;
    $this->currentRequest   = $requestStack->getCurrentRequest();
    $this->tempStore        = $tempStoreFactory->get('quizz');
    $this->currentQuizzId   = $this->getcurrentQuizzId();
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
   * get Current Quizz Id
   */
  private function getcurrentQuizzId() {

    if ($this->currentRequest->get('quizzId')) {
      $quizzId = $this->currentRequest->get('quizzId');
      $this->tempStore->set('quizz.id', $quizzId);
    } else {
      $quizzId = $this->tempStore->get('quizz.id');
    }

    if (is_null($quizzId)) {
      \Drupal::messenger()->addMessage('Quizz: bad parameters', 'error');
      return new RedirectResponse(\Drupal\Core\Url::fromRoute('<front>')->toString());
    }

    if (is_null($this->quizzManager->getQuizzById($quizzId))) {
      \Drupal::messenger()->addMessage('Quizz: The quizz is not available', 'error');
      return new RedirectResponse(\Drupal\Core\Url::fromRoute('<front>')->toString());
    }

    return $quizzId;
  }

  /**
   * pseudo
   *
   * @return array
   */
  public function pseudo() {

    $build['quizz_question_pseudo'] = [
      '#theme'  =>  'quizz_pseudo_template',
    ];

    return $build;
  }

  /**
   * save Pseudo
   */
  public function savePseudo() {

    $this->tempStore->set('quizz.pseudo', $this->currentRequest->get('quizz')['pseudo']);
  
    $errors = $this->validate();

    if (!empty($errors)) {
      return $this->sendErrorResponse($errors);
    }

    $questionId = $this->quizzManager->getQuestionsIds($this->currentQuizzId)[0];

    return new RedirectResponse(
      \Drupal\Core\Url::fromRoute('quizz.view', ['questionId' => $questionId])->toString()
    );
  }

  /**
   * view
   *
   * @param int $questionId
   * 
   * @return array
   */
  public function view(int $questionId) {
    $question   = $this->quizzManager->getQuestionById($questionId, $this->currentQuizzId);

    $errors     = $this->validate($question);

    if (!empty($errors) ) {
      return $this->sendErrorResponse($errors);
    }

    if ($question['timer'] > 0) {
      /**
       * in case of reloaded pages, do not reset timer for the current question Id
       */
      if (is_null($this->tempStore->get('quizz.timer.' . $questionId))) {
        $this->tempStore->set('quizz.timer.' . $questionId, new \DateTime("now"));
      }
    }

    list($questionId, $currentIndex, $nbrQuestions)  = $this->getNextQuestionInformationById($questionId);

    $build['quizz_question_pseudo'] = [
      '#theme'        => 'quizz_step_template',
      '#quizz'        => $question,
      '#currentIndex' => $currentIndex,
      '#nbrQuestions' => $nbrQuestions,
    ];

    return $build;
  }

  /**
   * Save
   *
   * @param int $questionId
   * @param int $answerId
   */
  public function save(int $questionId, int $answerId) {

    $question = $this->quizzManager->getQuestionById($questionId, $this->currentQuizzId);

    $errors = $this->validate($question, $answerId);

    if (!empty($errors)) {
      return $this->sendErrorResponse($errors);
    }

    $clientIp             = $this->currentRequest->getClientIp();
    $pseudo               = $this->tempStore->get('quizz.pseudo');
    list($nextQuestionId) = $this->getNextQuestionInformationById($questionId);

    $this->quizzManager->saveResult($questionId, $answerId, $clientIp, $pseudo, $this->currentQuizzId);

    if (is_null($nextQuestionId)) {
      $redirectUrl = \Drupal\Core\Url::fromRoute('quizz.result')->toString();
    } else {
      $redirectUrl = \Drupal\Core\Url::fromRoute('quizz.view', ['questionId' => $nextQuestionId])->toString();
    }

    return new RedirectResponse($redirectUrl);
  }

  /**
   * result
   * 
   * @return array
   */
  public function result() {

    $clientIp       = $this->currentRequest->getClientIp();
    $pseudo         = $this->tempStore->get('quizz.pseudo');
    $results        = $this->quizzManager->getResuls($clientIp, $pseudo, $this->currentQuizzId);
    $questions      = $this->quizzManager->getQuestions($this->currentQuizzId);
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
      return $this->sendErrorResponse((string) $this->t('Quizz: Cheater !'));
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
   * Get Next Question Information By Id
   * 
   * @param int $questionId question id
   * 
   * @return array
   */
  private function getNextQuestionInformationById(int $questionId) {
    $questionIds  = $this->quizzManager->getQuestionsIds($this->currentQuizzId);

    $currentIndex = array_search($questionId, $questionIds);
    $next         = $currentIndex++;
    return [
      (isset($questionIds[$next++])) ? $questionIds[$next++] : null,
      $currentIndex,
      count($questionIds)
    ];
  }

  /**
  * mixed $errors errors
  */
  private function sendErrorResponse($errors) {

    if (!is_array($errors)) {
      $errors = [$errors];
    }

    $redirectUrl = \Drupal\Core\Url::fromRoute('quizz.pseudo', ['quizzId' => $this->currentQuizzId])->toString();
    foreach ($errors as $error) {
      \Drupal::messenger()->addMessage($error, 'error');
    }
    return new RedirectResponse($redirectUrl);
  }

  /**
   * Validate
   *
   * @param  mixed  $question question
   * @param  mixed  $answerId answer id
   *
   * @return array
   */
  protected function validate($question = false, $answerId = null) {
    $errors   = [];
    $clientIp = $this->currentRequest->getClientIp();
    $pseudo   = $this->tempStore->get('quizz.pseudo');

    if (!$pseudo) {
      $errors[] = (string) $this->t('Quizz: Pseudo is required.');
    }

    if (is_null($question)) {
      $errors[] = (string) $this->t('Quizz: Question not found');
    }

    if ($question !== false) {
      if ($this->quizzManager->hasQuizzed($clientIp, $pseudo, $this->currentQuizzId, $question['question_id'])) {
        $errors[] = (string) $this->t('Quizz: You alreadty vote for this quizz');
      }

      if ($answerId != 0) {

        if ($question['timer'] > 0) {

          $timer  = $this->tempStore->get('quizz.timer.' . $question['question_id']);
          $this->tempStore->set('quizz.timer.' . $question['question_id'], null);

          $currentDate  = new \DateTime('now');
          $interval     = $currentDate->diff($timer);

          if (! $interval || ((int) $interval->format('%s') > (int) $question['timer'])) {
            $errors[] = (string) $this->t("Quizz: It looks like you've run out of time for a question");
          }
        }

        $answerIds = [];
        foreach ($question['answers'] as $answer) {
          $answerIds[] = $answer->qa_id;
        }

        if (!in_array($answerId, $answerIds)) {
          $errors[] = (string) $this->t('Quizz: No relationship between answer and question');
        }
      }
    }

    return $errors;
  }
}
