<?php

namespace Drupal\match_point\Form\Level;

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
	 * Level to delete
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
	    return 'match_point_admin_level';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion() {
		return $this->t('Do you really want to remove this level ?');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl() {
		return new Url('match_point.level');
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

		$this->connection->delete('match_point_level')
			->condition('id', $this->id)
			->execute();

		$this->messenger()->addMessage($this->t('The level has been deleted'));
		
		$response = Url::fromRoute('match_point.level');
		$form_state->setRedirectUrl($response); 
	}
}
