<?php

namespace Drupal\light_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Light' Block.
 *
 * @Block(
 *   id = "light_block",
 *   admin_label = @Translation("Light block")
 * )
 */
class LightBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
  	$config = $this->getConfiguration();

    return [
      '#markup' => $config['light_block_content'],
      '#allowed_tags' => ['script'],
    ];
  }

	/**
	* {@inheritdoc}
	*/
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['light_block_content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Content'),
      '#description' => $this->t('The content above will appear without any HTML decoration or strip tags functionnality'),
      '#default_value' => isset($config['light_block_content']) ? $config['light_block_content'] : '',
    ];

    return $form;
  }

   /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['light_block_content'] = $values['light_block_content'];
  }
}
