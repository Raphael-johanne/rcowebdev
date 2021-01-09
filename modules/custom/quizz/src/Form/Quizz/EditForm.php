<?php

namespace Drupal\quizz\Form\Quizz;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;

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
     * Quizz id
     *
     * @var int
     */
    protected $quizzId = null;

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
        return new Url('quizz.overview');
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
     * @param int|null           $quizzId
     *
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $this->quizzId     = $id;
        $editedQuizz       = $this->getQuizz();

        if ($id > 0 && !$editedQuizz) {
            throw new \Exception($this->t('quizz - The quizz provided does not exist'));
        }

        $form['quizz_name'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Quizz'),
            '#default_value'    => $editedQuizz,
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
        $name = $form_state->getValue('quizz');
        
        if ($this->answerId > 0) {
            $this->connection->update('quizz')
            ->fields(['name' => $name])
            ->condition('id', $this->answerId, "=")
            ->execute();

            $this->messenger()->addMessage($this->t('The quizz has been updated'));
        } else {
            $this->connection->insert('quizz')
            ->fields(['name' => $answer])
            ->execute();

            $this->messenger()->addMessage($this->t('The quizz has been created'));
        }

        $response = Url::fromRoute('quizz.overview');
        $form_state->setRedirectUrl($response);
    }

    /**
     *
     * get Quizz
     * 
     * @return mixed
     */
    private function getQuizz() {
        if (is_null($this->quizzId))
            return null;

        return $this->connection->select('quizz')
            ->fields('quizz', ['name'])
            ->condition('id', $this->quizzId, "=")
            ->execute()
            ->fetchAll()[0]
            ->name;
    }
}
