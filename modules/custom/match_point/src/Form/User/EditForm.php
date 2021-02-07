<?php

namespace Drupal\match_point\Form\User;

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
     * User id
     *
     * @var int
     */
    protected $userId = null;

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
        return new Url('match_point.overview');
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
        return 'match_point_admin_user';
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
        $this->userId       = $id;
        $editedUser         = $this->matchPointManager->getUserById($this->userId);
        $selectedNodes      = $this->getSelectedNodes(); 

        if ($id > 0 && !$editedUser) {
            throw new \Exception($this->t('Match point user - The user provided does not exist'));
        }
      
        $form['match_point_name'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('User'),
            '#default_value'    => $editedUser->name,
            '#required'         => true
        ];

        $form['match_point_picture'] = [
            '#type' => 'managed_file',
            '#title' => $this->t('Picture'),
            '#upload_location' => 'public://downloads',
            '#default_value'    => $editedUser->picture,
            '#upload_validators' => [
              'file_validate_extensions' => ['jpg', 'png'],
            ],
        ];

        $form['match_point_points'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Points'),
            '#default_value'    => $editedUser->points,
            '#required'         => true
        ];

        $nodes = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadByProperties(['type' => 'film', 'status' => 1]);

        
        $form['match_point_level_enable'] = [
            '#type'             => 'checkbox',
            '#title'            => $this->t('Use automatic point calculation by level'),
            '#default_value'    => true,
        ];

        $options = [];
        foreach ($nodes as $node) {
            $options[$node->id()] = $node->getTitle();
        }

        $form['match_point_node_id'] = [
            '#type'             => 'select',
            '#size'             => 25,
            '#multiple'         => true,
            '#title'            => $this->t('Select founded Films'),
            '#options'          => $options,
            '#default_value'    => $selectedNodes,
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
        $toSave     = ['name' => $form_state->getValue('match_point_name')];
        $picture    = $form_state->getValue('match_point_picture', 0);
        $points     = $form_state->getValue('match_point_points');
        $nodeIds    = $form_state->getValue('match_point_node_id');
        $id         = $this->userId;
        
        if ($form_state->getValue('match_point_level_enable'))  {
            $earnPoints = $this->matchPointManager->getEarnPointsByPoints($points);
            if (is_null($earnPoints)) {
                throw new \exception('getEarnPointsByPoints return a bad response');
            }
            $toSave['points'] = $points + $earnPoints;
        } else {
            $toSave['points'] = $points;
        }

        if (isset($picture[0]) && !empty($picture[0])) {
          $file = File::load($picture[0]);
          $file->setPermanent();
          $file->save();
          $toSave['picture'] = $file->getFilename();
        }
        
        if ($id > 0) {
            $this->connection->update('match_point_user')
            ->fields($toSave)
            ->condition('id', $id , "=")
            ->execute();

            $this->connection->delete('match_point_user_film')
            ->condition('user_id', $id , '=')
            ->execute();

            $this->messenger()->addMessage($this->t('The user has been updated'));
        } else {
            $id = $this->connection->insert('match_point_user')
            ->fields($toSave)
            ->execute();

            $this->messenger()->addMessage($this->t('The user has been created'));
        }

        $query = $this->connection->insert('match_point_user_film')->fields(['node_id', 'user_id']);
        foreach ($nodeIds as $nodeId) {
            $query->values([
                'node_id'   => $nodeId,
                'user_id'   => $id 
            ]);
        }
        
        $query->execute();

        $response = Url::fromRoute('match_point.overview');
        $form_state->setRedirectUrl($response);
    }

        /**
     * Get selected answers
     *
     * @return array
     */
    private function getSelectedNodes() {
        if (is_null($this->userId))
            return null;

        $d = [];

        $relations = $this->connection->select('match_point_user_film')
            ->fields('match_point_user_film', ['node_id'])
            ->condition('user_id', $this->userId, "=")
            ->execute()
            ->fetchAll();
         
        foreach ($relations as $relation) {
            $d[] = $relation->node_id;
        }

        return $d;
    }
}
