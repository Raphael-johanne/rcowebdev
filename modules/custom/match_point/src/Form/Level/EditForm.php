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
        return new Url('match_point.level.overview');
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
     * @param int|null           $answerId
     *
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $this->id     = $id;
        $edited       = $this->matchPointManager->getLevelById($this->id);

        if ($id > 0 && !$editeditededUser) {
            throw new \Exception($this->t('Match point user - The user provided does not exist'));
        }
      
        $form['match_point_points'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('From'),
            '#default_value'    => $edited->from,
            '#required'         => true
        ];

        $form['match_point_points'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('To'),
            '#default_value'    => $edited->to,
            '#required'         => true
        ];

        $form['match_point_points'] = [
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
            'name'      => $form_state->getValue('match_point_name'),
            'points'    => $this->getCalculatedPoints($form_state->getValue('match_point_points'))
            //'points'    => $form_state->getValue('match_point_points')
        ];


        if ($this->userId > 0) {
            $this->connection->update('match_point_level')
            ->fields($toSave)
            ->condition('id', $this->userId, "=")
            ->execute();

            $this->messenger()->addMessage($this->t('The level has been updated'));
        } else {
            $this->connection->insert('match_point_level')
            ->fields($toSave)
            ->execute();

            $this->messenger()->addMessage($this->t('The level has been created'));
        }

        $response = Url::fromRoute('match_point.level.overview');
        $form_state->setRedirectUrl($response);
    }

    /**
     * Get calculated points
     * 
     * @return int
     */
    private function getCalculatedPoints($p) {
        /**
         * formula
         * 
         * p    = current points of user
         * T    = total points of all users
         * e    = earn points of current user
         * v    = value of a point 
         * nb   = number of players  
         * 
         * p = t + ((1-(t/T))/ (nb - v))
         */
        $v = 1;
        $totalPointsInformations = $this->matchPointManager->getTotalPointsInformations();
        $T  = $totalPointsInformations->total;
        $nb = $totalPointsInformations->nb;
        

        $e = round(
            $p + ((1 - ($p/$T)) / ($nb - $v))
        );
        echo '<pre>';
        var_dump('e = $p + ((1 - ($p/$T)) / ($nb - $v))');
        if ($e == $p) {
            $k = $e == $p;
            var_dump("e == p ? " . $k);
            $e += 1;
        }
        
        var_dump("Total points = " . $T);
        var_dump("Old point = " . $p);
        var_dump("New point = " . $e);
        die('KO');  
        return $e;
    }

}
