<?php

namespace Drupal\match_point\Form\Winner;

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
	 * Winner to delete
	 *
	 * @var int
	 */
	protected $id = null;

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
	    return 'match_point_admin_winner';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion() {
		return $this->t('Do you really want to remove this winner ?');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl() {
		return new Url('match_point.winner');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
		$this->id = $id;
		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$this->connection->delete('match_point_winner')
			->condition('id', $this->id)
			->execute();

		$this->messenger()->addMessage($this->t('The winner has been deleted'));
		
		$response = Url::fromRoute('match_point.winner');
		$form_state->setRedirectUrl($response); 
	}
}
