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

    $rows = [];
    foreach ($this->quizzManager->getQuizzs() as $quizz) {
      $links = [];
      
      $row = [
        $quizz->id,
        $quizz->name,
        $quizz->available
      ];

      $links['edit'] = [
        'title' => $this->t('Edit'),
        'url'   => Url::fromRoute('quizz.edit', ['quizz_id' => $quizz->id]),
      ];
      $links['results'] = [
        'title' => $this->t('Results'),
        'url'   => Url::fromRoute('quizz.result.overview', ['quizz_id' => $quizz->id]),
      ];
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url'   => Url::fromRoute('quizz.delete', ['quizz_id' => $quizz->id]),
      ];

      $row[] = [
        'data' => [
          '#type'   => 'operations',
          '#links'  => $links,
        ],
      ];
      
      $rows[] = $row;
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
