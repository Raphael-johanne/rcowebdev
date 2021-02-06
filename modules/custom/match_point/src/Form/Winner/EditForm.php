<?php

namespace Drupal\match_point\Form\Winner;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\match_point\MatchPointManager;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Configure user edit form
 */
class EditForm extends FormBase {

    /**
     * matchPointManager
     *
     * @var \Drupal\match_point\MatchPointManager
     */
    protected $matchPointManager;

	/**
	 * The database connection.
	 *
	 * @var \Drupal\Core\Database\Connection
	 */
	protected $connection;

    /**
     * id
     *
     * @var int
     */
    protected $id = null;

    /**
     * Constructs
     *
     * @param \Drupal\match_point\MatchPointManager $matchPointManager
     * @param \Drupal\Core\Database\Connection      $connection
     */
    public function __construct(MatchPointManager $matchPointManager, Connection $connection) {
        $this->matchPointManager    = $matchPointManager;
        $this->connection           = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        return new Url('match_point.winner');
    }

    /**
     * create
     *
     * @param ContainerInterface $container
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('match_point.manager'),
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
     * build form
     * 
     * @param array              $form
     * @param FormStateInterface $form_state
     * @param int|null           $id
     *
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $this->id   = $id;
        $edited     = $this->matchPointManager->getWinnerById($this->id);

        if ($id > 0 && !$edited) {
            throw new \Exception($this->t('Match point winner - The winner provided does not exist'));
        }

        if (! $users = $this->matchPointManager->getUsers()) {
            $this->messenger()->addError($this->t('Match point winner - No user available'));
            $response = new RedirectResponse('/admin/match_point/winners');
            $response->send();
            return;
        }

        $winners = [];
        foreach ($users as $user) {
            $winners[$user->id] = $user->name;
        }

        $form['match_point_user_id'] = [
            '#type'             => 'select',
            '#title'            => $this->t('Select winner'),
            '#options'          => $winners,
            '#default_value'    => $edited->user_id,
            '#required'         => true
        ];
      
        $form['match_point_description'] = [
            '#type'             => 'textarea',
            '#title'            => $this->t('Description'),
            '#default_value'    => $edited->description,
            '#required'         => true
        ];

        $form['match_point_from'] = [
            '#type'             => 'date',
            '#title'            => $this->t('From'),
            '#default_value'    => $edited->from,
            '#required'         => true
        ];

        $form['match_point_to'] = [
            '#type'             => 'date',
            '#title'            => $this->t('to'),
            '#default_value'    => $edited->to,
            '#required'         => true
        ];

        $form['match_point_points'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Points'),
            '#default_value'    => $edited->points,
            '#required'         => true,
        ];

        $form['match_point_available'] = [
            '#type'             => 'checkbox',
            '#title'            => $this->t('Available'),
            '#default_value'    => $edited->available,
            '#required'         => false
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
        $toSave = [
            'user_id'       => $form_state->getValue('match_point_user_id'),
            'description'   => $form_state->getValue('match_point_description'),
            'points'        => $form_state->getValue('match_point_points'),
            'from'          => $form_state->getValue('match_point_from'),
            'to'            => $form_state->getValue('match_point_to'),
            'available'     => $form_state->getValue('match_point_available')
        ];

        if ($this->id > 0) {
            $this->connection->update('match_point_winner')
            ->fields($toSave)
            ->condition('id', $this->id, "=")
            ->execute();

            $this->messenger()->addMessage($this->t('The winner has been updated'));
        } else {
            $this->connection->insert('match_point_winner')
            ->fields($toSave)
            ->execute();

            $this->messenger()->addMessage($this->t('The winner has been created'));
        }

        $response = Url::fromRoute('match_point.winner');
        $form_state->setRedirectUrl($response);
    }
}
