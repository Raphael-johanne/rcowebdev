<?php

namespace Drupal\quizz\Form\Quizz;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Quizz\QuizzManager;

/**
 * Configure Quizz edit form    
 */
class EditForm extends FormBase {

	/**
	 * The database connection.
	 *
	 * @var \Drupal\Core\Database\Connection
	 */
	protected $connection;

    /**
     * \Drupal\Quizz\QuizzManager $quizzManager Quizz Manager
     */
    protected $quizzManager;

    /**
     * Quizz id
     *
     * @var int
     */
    protected $quizzId = null;

    /**
     * Constructs
	 * @param \Drupal\Core\Database\Connection $connection The database connection
     * @param \Drupal\Quizz\QuizzManager $quizzManager Quizz Manager
     */
    public function __construct(Connection $connection, QuizzManager $quizzManager) {
        $this->connection   = $connection;
        $this->quizzManager = $quizzManager;   
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        return new Url('quizz.overview');
    }

    /**
     * create
     *
     * @param ContainerInterface $container
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database'),
            $container->get('quizz.manager')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'quizz_admin';
    }

    /**
     * @param array              $form
     * @param FormStateInterface $form_state
     *
     * @return void
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

       /* 
      if (strlen($form_state->getValue('quizz_answer')) == 0) {
        $form_state->setErrorByName('quizz_answer', $this->t('You must specify an answer (alphanumeric).'));
      }
      */
    }

    /**
     * @param array              $form
     * @param FormStateInterface $form_state
     * @param int|null           $quizz_id
     *
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state, $quizz_id = null) {
        $this->quizzId      = $quizz_id;
        $editedQuizz        = ($this->quizzId) ? $this->quizzManager->getQuizzById($this->quizzId) : null;
        $selectedQuestions  = ($this->quizzId) ? $this->quizzManager->getSelectedQuestionsByQuizzId($this->quizzId) : null;
        
        $form['quizz_name'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Name'),
            '#default_value'    => $editedQuizz->name ?? $editedQuizz->name,
            '#required'         => true
        ];

        $form['quizz_available'] = [
            '#type'             => 'checkbox',
            '#title'            => $this->t('Available'),
            '#default_value'    => $editedQuizz->available ?? $editedQuizz->available,
            '#required'         => false
        ];
        
        /*
        $form['article'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Questions'),
            '#autocomplete_route_name' => 'quizz.manager.questions',
            "#multiple" => true
        ];
        */

        $form['quizz_questions'] = [
            '#type'             => 'select',
            '#size'             =>  10,
            '#multiple'         => true,
            '#title'            => $this->t('Select questions'),
            '#options'          => $this->quizzManager->getQuestions(),
            '#default_value'    => $selectedQuestions,
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
    public function submitForm(array &$form, FormStateInterface $form_state) 
    {
        $name       = $form_state->getValue('quizz_name');
        $available  = $form_state->getValue('quizz_available');
        $questions  = $form_state->getValue('quizz_questions');
        $query      = null;

        if ($this->quizzId > 0) {

            $this->connection->update('quizz')
                ->fields(['name' => $name, 'available' => $available])
                ->condition('id', $this->quizzId, "=")
                ->execute();
        
            $this->connection->delete('quizz_quizz_question')
            ->condition('quizz_id', $this->quizzId, '=')
            ->execute();

            $this->messenger()->addMessage($this->t('The quizz has been updated'));
        } else {
            $this->quizzId = $this->connection->insert('quizz')
                ->fields(['name' => $name, 'available' => $available])
                ->execute();

            $this->messenger()->addMessage($this->t('The quizz has been created'));
        }

        $query = $this->connection->insert('quizz_quizz_question')->fields(['quizz_id', 'question_id']);
        foreach ($questions as $questionId) {
            $query->values([
                'quizz_id'      => $this->quizzId,
                'question_id'   => $questionId
            ]);
        }

        $query->execute();

        $response = Url::fromRoute('quizz.overview');
        $form_state->setRedirectUrl($response);
    }
}
