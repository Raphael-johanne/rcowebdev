<?php

namespace Drupal\deploy\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure deploy settings
 */
class DeploySettingsForm extends ConfigFormBase {

    /**
     * @var \Drupal\deploy\DeployImport
     */
    protected $deployImport;

    /**
     * @param \Drupal\deploy\DeployImport $deployImport
     */
    public function __construct(
        \Drupal\deploy\DeployImport $deployImport
    ) {
        $this->deployImport = $deployImport;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('deploy.import')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'deploy_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'deploy.settings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('deploy.settings');

        $form['deploy_folder'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Folder'),
            '#default_value' => $config->get('deploy_folder'),
        );

        $form['deploy_archive_name'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Archive name'),
            '#default_value' => $config->get('deploy_archive_name'),
        );

        $form['deploy_folder_import'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Import archive after hitting <em>Save</em>'),
            '#description' => $this->t('This setting will deploy the archive now.'),
            '#default_value' => FALSE,
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->configFactory->getEditable('deploy.settings')
            ->set('deploy_folder', $form_state->getValue('deploy_folder'))
            ->save();

        $this->configFactory->getEditable('deploy.settings')
            ->set('deploy_archive_name', $form_state->getValue('deploy_archive_name'))
            ->save();

        if ($form_state->getValue('deploy_folder_import')) {
            try {
                $this->deployImport->import();
                $this->messenger()->addStatus($this->t('The site has been deployed, please clear cache'));
            } catch (\Exception $e) {
                $this->messenger()->addError($this->t(sprintf('An error occured %s', $e->getMessage())));
            }
        }

        parent::submitForm($form, $form_state);
    }
}
