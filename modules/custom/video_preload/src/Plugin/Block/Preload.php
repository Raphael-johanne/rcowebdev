<?php

namespace Drupal\video_preload\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Video Preload' Block.
 *
 * @Block(
 *   id = "video_preload_block",
 *   admin_label = @Translation("Video preload block")
 * )
 */
class Preload extends BlockBase implements BlockPluginInterface {

 /**
   * {@inheritdoc}
   */
  public function build() {
  	return [
      '#theme'  => 'preload',
      '#cache'  => ['max-age' => 0]
    ];
  }
	/**
	* {@inheritdoc}
	*/
  public function blockForm($form, FormStateInterface $form_state) {}

   /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {}
}
