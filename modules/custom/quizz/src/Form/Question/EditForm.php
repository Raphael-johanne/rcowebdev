<?php

namespace Drupal\quizz\Form\Question;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Configure question edit form
 */
class EditForm extends FormBase {

    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $connection;

    /**
     * Question id
     *
     * @var int
     */
    protected $questionId = null;

    /**
     * Construct
     *
     * @param \Drupal\Core\Database\Connection $connection The database connection
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
 
    /**
     * create
     *
     * @param ContainerInterface $container
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'quizz_admin_question';
    }

    /**
     * @param array              $form
     * @param FormStateInterface $form_state
     *
     * @return void
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      if (strlen($form_state->getValue('quizz_question')) == 0) {
        $form_state->setErrorByName('quizz_question', $this->t('You must specify a question (alphanumeric).'));
      }
    }

    /**
     * @param array              $form
     * @param FormStateInterface $form_state
     * @param int|null           $answerId
     *
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {

        $this->questionId   = $id;
        $editedQuestion     = $this->getQuestion();
        $selectedAnswers    = $this->getSelectedAnswers();
        $goodAnswerId       = $this->getGoodAnswer();

        if ($id > 0 && !$editedQuestion) {
            throw new \Exception($this->t('quizz Question - The question provided does not exist'));
        }
        /*
        $form['quizz'] = [
            '#type'             => 'select',
            '#multiple'         => true,
            '#title'            => $this->t('Select quizz'),
            '#options'          => $this->getQuizz(),
            '#default_value'    => $selectedAnswers,
            '#required'         => true
        ];
        */
        $form['quizz_question'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Question'),
            '#default_value'    => $editedQuestion,
            '#required'         => true
        ];

        $form['quizz_picture'] = [
            '#type' => 'managed_file',
            '#title' => $this->t('Picture'),
            '#upload_location' => 'public://downloads',
            '#upload_validators' => [
              'file_validate_extensions' => ['jpg', 'png'],
            ],
        ];

        $form['quizz_answers'] = [
            '#type'             => 'select',
            '#size' =>200,
            '#multiple'         => true,
            '#title'            => $this->t('Select answers'),
            '#options'          => $this->getAnswers(),
            '#default_value'    => $selectedAnswers,
            '#required'         => true
        ];

        $form['quizz_good_answer_id'] = [
            '#type'             => 'select',
            '#multiple'         => false,
            '#title'            => $this->t('Good answer'),
            '#options'          => $this->getAnswers(),
            '#default_value'    => $goodAnswerId,
            '#required'         => true
        ];

        $form['submit'] = [
            '#type'             => 'submit',
            '#title'            => $this->t('Save'),
            '#default_value'    => "Save",
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $question 	    = $form_state->getValue('quizz_question');
        $answers        = $form_state->getValue('quizz_answers');
        $id             = $this->questionId;
        $picture        = $form_state->getValue('quizz_picture', 0);
        $goodAnswerId   = $form_state->getValue('quizz_good_answer_id', 0);
        
        if (isset($picture[0]) && !empty($picture[0])) {
          $file = File::load($picture[0]);
          $file->setPermanent();
          $file->save();
          $picture = $file->getFilename();
        }
        
        $toSave = [
            'name' => $question, 
            'quizz_picture' => (!empty($picture)) ? $picture : null, 
            'quizz_good_answer_id' => $goodAnswerId
        ];

        if ($id > 0) {
            $this->connection->update('quizz_question')
            ->fields($toSave)
            ->condition('id', $id, "=")
            ->execute();

            $this->connection->delete('quizz_question_answer')
            ->condition('question_id', $id, '=')
            ->execute();

        } else {
            $id = $this->connection->insert('quizz_question')
            ->fields($toSave)
            ->execute();
        }
        
        $query = $this->connection->insert('quizz_question_answer')->fields(['question_id', 'answer_id']);
        foreach ($answers as $answerId) {
            $query->values([
                'question_id' => $id,
                'answer_id'   => $answerId
            ]);
        }
        

        $query->execute();

        //var_dump($answers);die;

        $response = Url::fromRoute('quizz.question.overview');
        $form_state->setRedirectUrl($response);
    }

    /**
     * @param  int|null $id
     *
     * @return mixed
     */
    private function getQuestion() {
        if (is_null($this->questionId))
            return null;

        return $this->connection->select('quizz_question')
            ->fields('quizz_question', ['name'])
            ->condition('id', $this->questionId, "=")
            ->execute()
            ->fetchAll()[0]
            ->name;
    }

    /**
     * Get answers
     *
     * @return array
     */
    private function getAnswers() {
        $d = [];

        $answers = $this->connection->select('quizz_answer')
            ->fields('quizz_answer')
            ->execute()
            ->fetchAll();

        foreach ($answers as $answer) {
            $d[$answer->id] = $answer->name;
        }

        return $d;
    }

    /**
     * Get quizz
     *
     * @return array
     */
    private function getQuizz() {
        $d = [];

        $relations = $this->connection->select('quizz')
            ->fields('id', ['id'])
            ->execute()
            ->fetchAll();
         
        foreach ($relations as $relation) {
            $d[] = $relation->id;
        }

        return $d;
    }

    /**
     * Get selected answers
     *
     * @return array
     */
    private function getSelectedAnswers() {
        if (is_null($this->questionId))
            return null;

        $d = [];

        $relations = $this->connection->select('quizz_question_answer')
            ->fields('quizz_question_answer', ['answer_id'])
            ->condition('question_id', $this->questionId, "=")
            ->execute()
            ->fetchAll();
         
        foreach ($relations as $relation) {
            $d[] = $relation->answer_id;
        }

        return $d;
    }

    /**
     * get Good Answer
     *
     * @return int
     */
    private function getGoodAnswer() {
        if (is_null($this->questionId))
            return null;

        $item = $this->connection->select('quizz_question')
            ->fields('quizz_question', ['quizz_good_answer_id'])
            ->condition('id', $this->questionId, "=")
            ->execute()
            ->fetchAll()[0]
            ->quizz_good_answer_id;
         
        return $item;
    }

    
}
