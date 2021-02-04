<?php

namespace Drupal\protected_pages_override\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a settings form for Protected Pages Override module.
 */
class ProtectedPagesOverrideConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'protected_pages_override.config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['protected_pages_override.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('protected_pages_override.settings');
    
    $form['caching'] = [
      '#type'   => 'fieldset',
      '#title'  => $this->t('Caching')
    ];

    $form['caching']['title'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'clearfix',
        ],
      ],
    ];

    $form['caching']['title']['enable'] = [
      '#type'           => 'checkbox',
      '#title'          => $this->t('Enable protected pages caching'),
      '#default_value'  => $config->get('enable'),
    ];

    $form['caching']['title']['spider_path'] = [
      '#type'           => 'textfield',
      '#title'          => $this->t('Path to spider'),
      '#default_value'  => $config->get('spider_path'),
    ];

    $form['caching']['title']['authorized_user_agent'] = [
      '#type'           => 'textfield',
      '#title'          => $this->t('Authorized user agent'),
      '#default_value'  => $config->get('authorized_user_agent'),
    ];

    $form['caching']['title']['spider_role_name'] = [
      '#type'           => 'textfield',
      '#title'          => $this->t('Drupal spider role machine name'),
      '#default_value'  => $config->get('spider_role_name'),
      '#description'    => $this->t('The role machine name for the spider, this role must have "bypass pages password protection" permission ON'),
    ];

    $form['caching']['title']['secret_key'] = [
      '#type'           => 'textfield',
      '#title'          => $this->t('Secret key'),
      '#default_value'  => $config->get('secret_key'),
      '#description'    => $this->t('The spider program used must send "Secret Key" in HTTP(S) HEADERS and this must match with protected pages override "secret_key" for security'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /**
     * @TODO
     */
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('protected_pages_override.settings');
    $form_values = $form_state->getValues();

    $config->set('spider_path', $form_values['spider_path'])
      ->set('authorized_user_agent', $form_values['authorized_user_agent'])
      ->set('spider_role_name', $form_values['spider_role_name'])
      ->set('secret_key', $form_values['secret_key'])
      ->set('enable', $form_values['enable'])
      ->save();
      
    parent::submitForm($form, $form_state);
  }

}
