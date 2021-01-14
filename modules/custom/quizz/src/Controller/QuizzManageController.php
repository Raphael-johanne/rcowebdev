<?php

namespace Drupal\quizz\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Quizz\QuizzManager;
/**
 *  Manage quizz controller.
 */
class QuizzManageController extends ControllerBase {

  /**
   * $quizzManager Quizz Manager

   *
   * @var \Drupal\Quizz\QuizzManager
   */
  protected $quizzManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('quizz.manager')
    );
  }

  /**
   * Construct
   *
   * @param \Drupal\Quizz\QuizzManager $quizzManager  Quizz Manager
   */
  public function __construct(QuizzManager $quizzManager) {
    $this->quizzManager = $quizzManager;
  }

  /**
  * overview
   */
  public function overview() {

    $rows = [];

    $header = [
      [
        'data'  => $this->t('ID'),
        'field' => 'id',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Name'),
        'field' => 'name',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Available'),
        'field' => 'available',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data'  => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    foreach ($this->quizzManager->getQuizzs() as $quizz) {
      $rows[] = [
        'data' => [
          $quizz->id,
            $this->t($quizz->name),
            $this->t($quizz->available),
            $this->l($this->t('Edit'), new Url('quizz.edit', ['quizz_id' => $quizz->id])),
            $this->l($this->t('Results'), new Url('quizz.result.overview', ['quizz_id' => $quizz->id])),
            $this->l($this->t('Delete'), new Url('quizz.delete', ['quizz_id' => $quizz->id])),
        ]
      ];
    }

    $build['quizz_table'] = [
      '#type' 	=> 'table',
      '#header' => $header,
      '#rows' 	=> $rows,
      '#empty' 	=> $this->t('No quizz available.'),
    ];

    return $build;
  }
}
