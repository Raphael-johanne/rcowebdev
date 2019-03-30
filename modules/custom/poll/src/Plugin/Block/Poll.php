<?php

namespace Drupal\poll\Plugin\Block;

use Drupal\Poll\PollManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a Poll Block.
 *
 * @Block(
 *   id = "poll_block",
 *   admin_label = @Translation("Poll block")
 * )
 */
class Poll extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  const RESULT_TEMPLATE   = 'poll_result_template';

  const DEFAULT_TEMPLATE  = 'poll_template';

  /**
   * @var Drupal\Poll\PollManagerInterface
   */
  protected $pollManager;

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
    PollManager $pollManager, 
    RequestStack $request_stack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pollManager  = $pollManager;
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
      $container->get('poll.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config     = $this->getConfiguration();
    $questionId = $this->configuration['poll_question'];
    $poll       = $this->pollManager->getPoll($questionId);
    $clientIp   = $this->requestStack->getCurrentRequest()->getClientIp();
    $theme      = self::DEFAULT_TEMPLATE;
    $total      = 0;

    if (is_null($poll)) {
      throw new \Exception(sprintf("Poll is not available for question id [%d]", $questionId));
    }

    if ($this->pollManager->hasPolled($questionId, $clientIp)) {
      $theme = self::RESULT_TEMPLATE;

      foreach ($poll['answers'] as $answer) {
        $total += $answer->nbr;
      }
    }

  	return [
      '#theme'  => $theme,
      '#poll'   => $poll,
      '#total'  => $total,
      '#cache'  => ['max-age' => 0]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form       = parent::blockForm($form, $form_state);
    $config     = $this->getConfiguration();
    $questions  = $this->pollManager->getQuestions();
    $options    = [];

    foreach ($questions as $question) {
      $options[$question->id] = $question->name;
    }
    
    $form['poll_question'] = [
      '#type'           => 'select',
      '#title'          => $this->t('Question'),
      '#description'    => $this->t('Select the question'),
      '#options'        => $options,
      '#default_value'  => isset($config['poll_question']) ? $config['poll_question'] : '',
    ];

    return $form;
  }

   /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['poll_question'] = $values['poll_question'];
  }
}
