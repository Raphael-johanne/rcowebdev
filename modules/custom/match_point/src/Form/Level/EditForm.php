<?php

namespace Drupal\match_point\Form\Level;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\match_point\MatchPointManager;

/**
 * Configure level edit form
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
     * User id
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
        return new Url('match_point.level');
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
        return 'match_point_admin_level';
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
        $this->id     = $id;
        $edited       = $this->matchPointManager->getLevelById($this->id);

        if ($id > 0 && !$edited) {
            throw new \Exception($this->t('Match point level - The level provided does not exist'));
        }
      
        $form['match_point_from'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('From'),
            '#default_value'    => $edited->from,
            '#required'         => true
        ];

        $form['match_point_to'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('To'),
            '#default_value'    => $edited->to,
            '#required'         => true
        ];

        $form['match_point_earn'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Earn Points'),
            '#default_value'    => $edited->points,
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
        $toSave     = [
            'from'      => $form_state->getValue('match_point_from'),
            'to'        => $form_state->getValue('match_point_to'),
            'points'    => $form_state->getValue('match_point_earn'),
        ];

        if ($this->id > 0) {
            $this->connection->update('match_point_level')
            ->fields($toSave)
            ->condition('id', $this->id, "=")
            ->execute();

            $this->messenger()->addMessage($this->t('The level has been updated'));
        } else {
            $this->connection->insert('match_point_level')
            ->fields($toSave)
            ->execute();

            $this->messenger()->addMessage($this->t('The level has been created'));
        }

        $response = Url::fromRoute('match_point.level');
        $form_state->setRedirectUrl($response);
    }

}
