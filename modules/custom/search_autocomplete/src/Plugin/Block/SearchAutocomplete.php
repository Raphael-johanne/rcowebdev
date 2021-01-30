<?php

namespace Drupal\search_autocomplete\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use \Drupal\taxonomy\Entity\Vocabulary;
use \Drupal\node\Entity\NodeType;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config   = $this->getConfiguration();

  	return [
      '#theme'                => self::DEFAULT_TEMPLATE,
      '#cache'                => ['max-age' => 0],
      '#node_enable'          => $config['node_enable'],
      '#node_type'            => $config['node_type'],
      '#taxonomy_enable'      => $config['taxonomy_enable'],
      '#taxonomy_vocabulary'  => $config['taxonomy_vocabulary'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form         = parent::blockForm($form, $form_state);
    $config       = $this->getConfiguration();
    $vocabularies = Vocabulary::loadMultiple();
    $types        = NodeType::loadMultiple();

    $form['fieldset_node'] = [
      '#type'           => 'fieldset',
      '#title'          => $this->t('Node')
    ];
    
    $form['fieldset_node']['node_enable'] = [
      '#type'           => 'checkbox',
      '#title'          => $this->t('Search on node title enable'),
      '#default_value'  => isset($config['node_enable']) ? $config['node_enable'] : '',
    ];

    $options = [];  
    foreach ($types as $type) {
      $options[$type->id()] =  $type->get('name');
    }

    $form['fieldset_node']['node_type'] = [
      '#type'           => 'select',
      '#title'          => $this->t('Type'),
      '#options'        => $options,
      '#default_value'  => isset($config['node_type']) ? $config['node_type'] : '',
    ];

    $form['fieldset_taxonomy'] = [
      '#type'           => 'fieldset',
      '#title'          => $this->t('Taxonomy')
    ];

    $form['fieldset_taxonomy']['taxonomy_enable'] = [
      '#type'           => 'checkbox',
      '#title'          => $this->t('Search on taxonomy term name enable'),
      '#default_value'  => isset($config['taxonomy_enable']) ? $config['taxonomy_enable'] : '',
    ];

    $options = [];
    foreach ($vocabularies as $vocabulary) {
      $options[$vocabulary->id()] = $vocabulary->get('name');
    }

    $form['fieldset_taxonomy']['taxonomy_vocabulary'] = [
      '#type'           => 'select',
      '#title'          => $this->t('Vocabulary'),
      '#options'        => $options,
      '#default_value'  => isset($config['taxonomy_vocabulary']) ? $config['taxonomy_vocabulary'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['node_enable']         = $values["fieldset_node"]['node_enable'];
    $this->configuration['node_type']           = $values["fieldset_node"]['node_type'];
    $this->configuration['taxonomy_enable']     = $values['fieldset_taxonomy']['taxonomy_enable'];
    $this->configuration['taxonomy_vocabulary'] = $values['fieldset_taxonomy']['taxonomy_vocabulary'];
  }
}
