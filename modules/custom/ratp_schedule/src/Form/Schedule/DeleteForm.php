<?php

namespace Drupal\ratp_schedule\Form\Schedule;

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
	 * schedule to delete
	 *
	 * @var int
	 */
	protected $scheduleId = null;

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
	    return 'ratp_schedule_admin';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion() {
		return $this->t('Do you really want to remove this schedule ?');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelUrl() {
		return new Url('ratp_schedule.overview');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
		$this->scheduleId = $id;
		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		
		$this->connection->delete('ratp_schedule')
			->condition('ratp_schedule_id', $this->scheduleId)
			->execute();

		$this->messenger()->addMessage($this->t('The schedule has been deleted'));
		
		
		$response = Url::fromRoute('ratp_schedule.overview');
		$form_state->setRedirectUrl($response); 
	}
}
