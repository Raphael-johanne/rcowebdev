<?php

namespace Drupal\search_autocomplete\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a autocomplete block.
 *
 * @Block(
 *   id = "search_autocomplete_block",
 *   admin_label = @Translation("Search autocomplete block")
 * )
 */
class SearchAutocomplete extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  const DEFAULT_TEMPLATE  = 'search_autocomplete';

  /**
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration, 
    $plugin_id, 
    $plugin_definition, 
    RequestStack $request_stack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config   = $this->getConfiguration();
    
  	return [
      '#theme'            => self::DEFAULT_TEMPLATE,
      '#cache'            => ['max-age' => 0],
      '#error_message'    => $config['bug_report_error_message'],
      '#success_message'  => $config['bug_report_success_message'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form     = parent::blockForm($form, $form_state);
    $config   = $this->getConfiguration();

    $form['bug_report_error_message'] = [
      '#type'           => 'textarea',
      '#title'          => $this->t('Error message'),
      '#default_value'  => isset($config['bug_report_error_message']) ? $config['bug_report_error_message'] : '',
    ];

    $form['bug_report_success_message'] = [
      '#type'           => 'textarea',
      '#title'          => $this->t('Success message'),
      '#default_value'  => isset($config['bug_report_success_message']) ? $config['bug_report_success_message'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['bug_report_error_message']    = $values['bug_report_error_message'];
    $this->configuration['bug_report_success_message']  = $values['bug_report_success_message'];
  }
}
