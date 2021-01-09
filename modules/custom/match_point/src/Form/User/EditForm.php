<?php

namespace Drupal\match_point\Form\User;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Configure answer edit form
 */
class EditForm extends FormBase {

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
     * @param \Drupal\Core\Database\Connection $connection The database connection
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
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
     * @param array              $form
     * @param FormStateInterface $form_state
     * @param int|null           $answerId
     *
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $this->userId     = $id;
        $editedUser       = $this->getUser();
        
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
            'points'    => $form_state->getValue('match_point_points')
        ];

        $picture    = $form_state->getValue('match_point_picture', 0);
      
        if (isset($picture[0]) && !empty($picture[0])) {
          $file = File::load($picture[0]);
          $file->setPermanent();
          $file->save();
          $toSave['picture'] = $file->getFilename();
        }

        if ($this->userId > 0) {
            $this->connection->update('match_point_user')
            ->fields($toSave)
            ->condition('id', $this->userId, "=")
            ->execute();

            $this->messenger()->addMessage($this->t('The user has been updated'));
        } else {
            $this->connection->insert('match_point_user')
            ->fields($toSave)
            ->execute();

            $this->messenger()->addMessage($this->t('The user has been created'));
        }

        $response = Url::fromRoute('match_point.overview');
        $form_state->setRedirectUrl($response);
    }

    /**
     *
     * @return mixed
     */
    private function getUser() {
        if (is_null($this->userId))
            return null;

        return $this->connection->select('match_point_user')
            ->fields('match_point_user', 
                [
                    'name',
                    'picture',
                    'points'
                ]
            )
            ->condition('id', $this->userId, "=")
            ->execute()
            ->fetchAll()[0];
    }
}
