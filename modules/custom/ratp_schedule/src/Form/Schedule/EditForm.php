<?php

namespace Drupal\ratp_schedule\Form\Schedule;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;

/**
 * Configure schedule edit form
 */
class EditForm extends FormBase {

    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $connection;

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
        return new Url('ratp_schedule.overview');
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
     * @param array              $form
     * @param FormStateInterface $form_state
     * @param int|null           $answerId
     *
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $this->scheduleId       = $id;
        $editedSchedule         = $this->getSchedule();

        if ($id > 0 && !$editedSchedule) {
            throw new \Exception($this->t('Ratp Schedule - The schedule provided does not exist'));
        }

        $form['ratp_schedule_name'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Name'),
            '#default_value'    => (isset($editedSchedule->ratp_schedule_name)) ? $editedSchedule->ratp_schedule_name : "",
            '#required'         => true,
            '#description'      => $this->t('Will appears on frontend to choose witch transport')
        ];

        $form['ratp_schedule_type'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Type'),
            '#default_value'    => (isset($editedSchedule->ratp_schedule_type)) ? $editedSchedule->ratp_schedule_type : "",
            '#required'         => true,
            '#description'      => $this->t('For the moment, only bus (B) are available')
        ];

        $form['ratp_schedule_number'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Number'),
            '#default_value'    => (isset($editedSchedule->ratp_schedule_number)) ? $editedSchedule->ratp_schedule_number : "",
            '#required'         => true,
            '#description'      => $this->t('Line number (Metro, rer, bus or anywhat else')
        ];

        $form['ratp_schedule_terminus_1'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Terminus 1'),
            '#default_value'    => (isset($editedSchedule->ratp_schedule_terminus_1)) ? $editedSchedule->ratp_schedule_terminus_1 : "",
            '#required'         => true,
            '#description'      => ""
        ];

        $form['ratp_schedule_terminus_2'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Terminus 2'),
            '#default_value'    => (isset($editedSchedule->ratp_schedule_terminus_2)) ? $editedSchedule->ratp_schedule_terminus_2 : "",
            '#required'         => true,
            '#description'      => ""
        ];

        $form['ratp_schedule_station_departure'] = [
            '#type'             => 'textfield',
            '#title'            => $this->t('Station departure'),
            '#default_value'    => (isset($editedSchedule->ratp_schedule_station_departure)) ? $editedSchedule->ratp_schedule_station_departure : "",
            '#required'         => true,
            '#description'      => ""
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
        $fields = [
            'ratp_schedule_name'                => $form_state->getValue('ratp_schedule_name'),
            'ratp_schedule_type'                => $form_state->getValue('ratp_schedule_type'),
            'ratp_schedule_number'              => $form_state->getValue('ratp_schedule_number'),
            'ratp_schedule_terminus_1'          => $form_state->getValue('ratp_schedule_terminus_1'),
            'ratp_schedule_terminus_2'          => $form_state->getValue('ratp_schedule_terminus_2'),
            'ratp_schedule_station_departure'   => $form_state->getValue('ratp_schedule_station_departure'),
        ];
        
        if ($this->scheduleId > 0) {
            $this->connection->update('ratp_schedule')
            ->fields($fields)
            ->condition('ratp_schedule_id', $this->scheduleId, "=")
            ->execute();

            $this->messenger()->addMessage($this->t('The schedule has been updated'));
        } else {
            $this->connection->insert('ratp_schedule')
            ->fields($fields)
            ->execute();

            $this->messenger()->addMessage($this->t('The schedule has been created'));
        }

        $response = Url::fromRoute('ratp_schedule.overview');
        $form_state->setRedirectUrl($response);
    }

    /**
     *
     * @return mixed
     */
    private function getSchedule() {
        if (is_null($this->scheduleId))
            return null;

        return $this->connection->select('ratp_schedule')
            ->fields('ratp_schedule', 
            [
                'ratp_schedule_name', 
                'ratp_schedule_type', 
                'ratp_schedule_number',
                'ratp_schedule_terminus_1',
                'ratp_schedule_terminus_2',
                'ratp_schedule_station_departure'
                ]
            )
            ->condition('ratp_schedule_id', $this->scheduleId, "=")
            ->execute()
            ->fetchAll()[0];
    }
}
