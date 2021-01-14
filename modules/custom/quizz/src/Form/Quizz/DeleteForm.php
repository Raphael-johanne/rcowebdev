<?php

namespace Drupal\quizz\Form\Quizz;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;

class DeleteForm extends ConfirmFormBase {

	/**
	 * The database connection.
	 *
	 * @var \Drupal\Core\Database\Connection
	 */
	protected $connection;

	/**
	 * quizz to delete
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
	 * {@inheritdoc}
	 */
	public function getQuestion() {
		return $this->t('Do you really want to remove this quizz ?');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl() {
		return new Url('quizz.overview');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $quizz_id = null) {
		$this->quizzId = $quizz_id;
		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
	
		$this->connection->delete('quizz_quizz_question')
			->condition('quizz_id', $this->quizzId)
			->execute();

		$this->connection->delete('quizz_result')
		->condition('quizz_id', $this->quizzId)
		->execute();

		$this->connection->delete('quizz')
			->condition('id', $this->quizzId)
			->execute();

		$this->messenger()->addMessage($this->t('The quizz has been deleted'));
		
		$response = Url::fromRoute('quizz.overview');
		$form_state->setRedirectUrl($response); 
	}
}
