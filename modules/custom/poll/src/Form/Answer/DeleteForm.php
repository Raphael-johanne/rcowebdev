<?php

namespace Drupal\Poll\Form\Answer;

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
	 * Answer to delete
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
	 * {@inheritdoc}
	 */
	public function getQuestion() {
		return $this->t('Do you really want to remove this answer ?');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl() {
		return new Url('poll.answer.overview');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
		$this->answerId = $id;
		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		try {
			$this->connection->delete('poll_question_answer')
				->condition('answer_id', $this->answerId)
				->execute();

			$this->connection->delete('poll_answer')
				->condition('id', $this->answerId)
				->execute();

			$this->messenger()->addMessage($this->t('The answer has been deleted'));
		} catch (\Exception $e) {
			$this->messenger()->addMessage($this->t('You cannot removed answer already populated in poll results'), 'error');
		}
		
		$response = Url::fromRoute('poll.answer.overview');
		$form_state->setRedirectUrl($response); 
	}
}
