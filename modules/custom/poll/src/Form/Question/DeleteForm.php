<?php

namespace Drupal\Poll\Form\Question;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;

class DeleteForm extends ConfirmFormBase
{
    
    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $connection;
    
    /**
     * Question to delete
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
        return new static($container->get('database'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'poll_admin_question';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getQuestion() {
        return $this->t('Do you really want to remove this question ?');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        return new Url('poll.question.overview');
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $this->questionId = $id;
        return parent::buildForm($form, $form_state);
    }
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        try {
            $this->connection->delete('poll_question')->condition('id', $this->questionId)->execute();
            $this->connection->delete('poll_question_answer')->condition('question_id', $this->questionId, "=")->execute();
        
            $this->messenger()->addMessage($this->t('The question has been deleted'));
        } catch (\Exception $e) {
            $this->messenger()->addMessage($this->t('You cannot removed question already populated in poll results or poll answers relationship'), 'error');
        }
       
        $response = Url::fromRoute('poll.question.overview');
        $form_state->setRedirectUrl($response);
    }
}
