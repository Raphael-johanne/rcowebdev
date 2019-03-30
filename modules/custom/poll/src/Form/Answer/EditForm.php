<?php

namespace Drupal\Poll\Form\Answer;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;

/**
 * Configure answer edit form
 */
class EditForm extends FormBase {

    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $connection;

    /**
     * Answer id
     *
     * @var int
     */
    protected $answerId = null;

    /**
     * Constructs
     *
     * @param \Drupal\Core\Database\Connection $connection The database connection
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        return new Url('poll.answer.overview');
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
        return 'poll_admin_answer';
    }

    /**
     * @param array              $form
     * @param FormStateInterface $form_state
     *
     * @return void
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      if (strlen($form_state->getValue('poll_answer')) == 0) {
        $form_state->setErrorByName('poll_answer', $this->t('You must specify an answer (alphanumeric).'));
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
        $this->answerId     = $id;
        $editedAnswer       = $this->getAnswer();

        if ($id > 0 && !$editedAnswer) {
            throw new \Exception($this->t('Poll Answer - The answer provided does not exist'));
        }

        $form['poll_answer'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Answer'),
            '#default_value'    => $editedAnswer,
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
        $answer = $form_state->getValue('poll_answer');
        
        if ($this->answerId > 0) {
            $this->connection->update('poll_answer')
            ->fields(['name' => $answer])
            ->condition('id', $this->answerId, "=")
            ->execute();

            $this->messenger()->addMessage($this->t('The answer has been updated'));
        } else {
            $this->connection->insert('poll_answer')
            ->fields(['name' => $answer])
            ->execute();

            $this->messenger()->addMessage($this->t('The answer has been created'));
        }

        $response = Url::fromRoute('poll.answer.overview');
        $form_state->setRedirectUrl($response);
    }

    /**
     *
     * @return mixed
     */
    private function getAnswer() {
        if (is_null($this->answerId))
            return null;

        return $this->connection->select('poll_answer')
            ->fields('poll_answer', ['name'])
            ->condition('id', $this->answerId, "=")
            ->execute()
            ->fetchAll()[0]
            ->name;
    }
}
