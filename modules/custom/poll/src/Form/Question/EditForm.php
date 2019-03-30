<?php

namespace Drupal\Poll\Form\Question;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;

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
        return 'poll_admin_question';
    }

    /**
     * @param array              $form
     * @param FormStateInterface $form_state
     *
     * @return void
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      if (strlen($form_state->getValue('poll_question')) == 0) {
        $form_state->setErrorByName('poll_question', $this->t('You must specify a question (alphanumeric).'));
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

        if ($id > 0 && !$editedQuestion) {
            throw new \Exception($this->t('Poll Question - The question provided does not exist'));
        }

        $form['poll_question'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Question'),
            '#default_value'    => $editedQuestion,
            '#required'         => true
        ];

        $form['poll_answers'] = [
            '#type'             => 'select',
            '#multiple'         => true,
            '#title'            => $this->t('Select answers'),
            '#options'          => $this->getAnswers(),
            '#default_value'    => $selectedAnswers,
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
        $question 	= $form_state->getValue('poll_question');
        $answers    = $form_state->getValue('poll_answers');
        $id         = $this->questionId;

        if ($id > 0) {
            $this->connection->update('poll_question')
            ->fields(['name' => $question])
            ->condition('id', $id, "=")
            ->execute();

            $this->connection->delete('poll_question_answer')
            ->condition('question_id', $id, '=')
            ->execute();

        } else {
            $id = $this->connection->insert('poll_question')
            ->fields(['name' => $question])
            ->execute();
        }

        $query = $this->connection->insert('poll_question_answer')->fields(['question_id', 'answer_id']);
        foreach ($answers as $answerId) {
            $query->values([
                'question_id' => $id,
                'answer_id'   => $answerId
            ]);
        }

        $query->execute();

        $response = Url::fromRoute('poll.question.overview');
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

        return $this->connection->select('poll_question')
            ->fields('poll_question', ['name'])
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

        $answers = $this->connection->select('poll_answer')
            ->fields('poll_answer')
            ->execute()
            ->fetchAll();

        foreach ($answers as $answer) {
            $d[$answer->id] = $answer->name;
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

        $relations = $this->connection->select('poll_question_answer')
            ->fields('poll_question_answer', ['answer_id'])
            ->condition('question_id', $this->questionId, "=")
            ->execute()
            ->fetchAll();
         
        foreach ($relations as $relation) {
            $d[] = $relation->answer_id;
        }

        return $d;
    }
}
