<?php

namespace Drupal\managepackvhsost\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a managepackvhsost form.
 */
trait SubscribeBuyPackSteps {
  
  protected function loadLayout(&$form) {
    /**
     *
     * @var \Drupal\Core\Layout\LayoutPluginManager $layoutPluginManager
     */
    $layoutPluginManager = \Drupal::service('plugin.manager.core.layout');
    
    /**
     *
     * @var \Drupal\managepackvhsost\Plugin\Layout\Sections\StaticPricing $instance
     */
    $instance = $layoutPluginManager->createInstance('managepackvhsost_static_pricing', []);
    $regions = [];
    $form['header'] = $instance->build($regions);
  }
  
  /**
   * Permet de se place directement sur l'etape du domaine.
   */
  protected function goToStepeDomain(&$form, FormStateInterface $form_state) {
    if (!$form_state->has('page_num') && !empty($_GET['type_pack'])) {
      if (array_key_exists($_GET['type_pack'], $this->type_packs)) {
        $form_state->set('page_num', 2);
        $form_state->set([
          'tempValues',
          1
        ], [
          'domaine' => $_GET['type_pack']
        ]);
      }
    }
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return []
   */
  public function selectPreviewsCallback(array $form, FormStateInterface $form_state) {
    return $form;
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return []
   */
  public function selectNextCallback(array $form, FormStateInterface $form_state) {
    return $form;
  }
  
  /**
   * On incremente page_num et on doit faire une sauvegarde des données de
   * l'etape precedante.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function selectPreviewSubmit(array $form, FormStateInterface $form_state) {
    $n = $form_state->get('page_num', 1);
    
    if ($n > 1)
      $form_state->set('page_num', $n - 1)->setRebuild(TRUE);
    else
      $form_state->set('page_num', 1)->setRebuild(TRUE);
  }
  
  /**
   * --
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function selectNextSubmit(array $form, FormStateInterface $form_state) {
    $n = $form_state->get('page_num', 1);
    
    $form_state->set([
      'tempValues',
      $n
    ], $form_state->getValues());
    
    if ($n < self::$max_stape)
      $form_state->set('page_num', $n + 1)->setRebuild(TRUE);
    else
      $form_state->set('page_num', $n)->setRebuild(TRUE);
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  function getPrice(array $form, FormStateInterface $form_state) {
    $tempValue = $form_state->get([
      'tempValues',
      4
    ]);
    
    if (!empty($tempValue["periode"])) {
      switch ($tempValue["periode"]) {
        case "p2y":
          return 192;
          break;
        case "p1y":
          return 399.9;
          break;
        case "p1m":
          return 39.99;
          break;
        default:
          return 5;
          break;
      }
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    // $form_state->setRedirect('<front>');
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function fieldsPaiement(array &$form, FormStateInterface $form_state) {
    $form['paiements'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'row'
        ]
      ],
      '#weight' => 20
    ];
    $form['paiements']['left'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'col-md-12'
        ]
      ]
    ];
    $form['paiements']['right'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'col-md-12'
        ]
      ]
    ];
    $form['paiements']['left']['paiement-stripe'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [],
        'id' => 'payment-element'
      ],
      '#weight' => 20
    ];
    //
    $form['paiements']['left']['error-message'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'error--message'
        ],
        'id' => '#error-message'
      ],
      '#weight' => 21
    ];
    //
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function actionsButtons(array &$form, FormStateInterface $form_state) {
    $form['container_buttons'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'd-flex',
          'justify-content-around',
          'align-items-center',
          'actions-buttons'
        ]
      ],
      '#weight' => 45
    ];
    if ($form_state->get('page_num') > 1)
      $form['container_buttons']['previews'] = [
        '#type' => 'submit',
        '#value' => 'Precedent',
        '#button_type' => 'secondary',
        '#submit' => [
          [
            $this,
            'selectPreviewSubmit'
          ]
        ],
        '#ajax' => [
          'callback' => '::selectPreviewsCallback',
          'wrapper' => $this->getFormId(),
          'effect' => 'fade',
          'event' => 'click'
        ],
        '#attributes' => [
          'class' => [
            'd-inline-block',
            'w-auto',
            'btn btn-outline-secondary'
          ]
        ]
      ];
    
    if ($form_state->get('page_num') < self::$max_stape) {
      $text = 'Suivant';
      if ($form_state->get('page_num') == 1)
        $text = 'Commencer';
      $form['container_buttons']['next'] = [
        '#type' => 'submit',
        '#value' => $text,
        '#button_type' => 'secondary',
        '#submit' => [
          [
            $this,
            'selectNextSubmit'
          ]
        ],
        '#ajax' => [
          'callback' => '::selectNextCallback',
          'wrapper' => $this->getFormId(),
          'effect' => 'fade',
          'event' => 'click'
        ],
        '#attributes' => [
          'class' => [
            'd-inline-block',
            'w-auto',
            'h-auto',
            'btn-primary',
            'btn rounded-pill'
          ],
          'data-trigger' => 'run'
        ]
      ];
    }
    
    if ($form_state->get('page_num') == self::$max_stape) {
      $form['container_buttons']['actions'] = [
        '#type' => 'actions'
      ];
      $form['container_buttons']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t("Payer votre forfait"),
        '#button_type' => 'secondary',
        '#attributes' => [
          'class' => [
            'btn-success'
          ]
        ]
      ];
    }
  }
  
}