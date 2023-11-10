<?php

namespace Drupal\managepackvhsost\Form;

use Drupal\Core\Form\FormStateInterface;
use Stephane888\Debug\ExceptionDebug;
use Drupal\Component\Utility\Html;
use Stephane888\Debug\Repositories\ConfigDrupal;

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
      }
    }
    ExceptionDebug::exception("Error to get price");
    // For test, il faut absolument supprimer cette ligne et
    // renvoyer une erreur.
    // return 8;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function fieldsPaiement(array &$form, FormStateInterface $form_state, $price, $paimentIndent) {
    $config = ConfigDrupal::config('stripebyhabeuk.settings');
    $request = \Drupal::requestStack()->getCurrentRequest();
    $form['paiement-info'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'mb-5',
          'd-flex',
          'flex-column align-items-center'
        ]
      ],
      
      [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => 'Payer votre commande '
      ],
      [
        '#type' => "html_tag",
        '#tag' => "strong",
        '#attributes' => [
          'class' => [
            'h4',
            'font-weight-bold'
          ]
        ],
        "#value" => "Total: " . $price . " €"
      ]
    ];
    $form['paiement-info-img'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'd-flex',
          'flex-column align-items-center'
        ]
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => '/' . drupal_get_path('module', 'managepackvhsost') . '/img/us-available-brands.e0ae81a0.svg',
          'class' => [
            'img-fluid',
            'mb-4',
            'd-none'
          ]
        ]
      ]
    ];
    $idHtml = Html::getUniqueId('cart-ifs-' . rand(100, 999));
    $form['titre_cart'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      "#attributes" => [
        'class' => []
      ],
      '#value' => t('Enter credit card information')
    ];
    $form['stripebyhabeuk_payment_intent_id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'payment-intent-id' . $idHtml
      ]
    ];
    $form['cart_information'] = [
      '#type' => 'html_tag',
      '#tag' => 'section',
      "#attributes" => [
        'id' => $idHtml,
        'class' => [
          'border',
          'mb-3',
          'p-4'
        ]
      ]
    ];
    $form['cart_information'] = [
      '#type' => 'html_tag',
      '#tag' => 'section',
      "#attributes" => [
        'id' => $idHtml,
        'class' => [
          'border',
          'mb-3',
          'p-4'
        ]
      ]
    ];
    $form['#attached']['library'][] = 'stripebyhabeuk/stripejsinit';
    // pass and attach datas.
    $form['#attached']['drupalSettings']['stripebyhabeuk'] = [
      'publishableKey' => $config['api_key_test'],
      'idhtml' => $idHtml,
      'enable_credit_card_logos' => FALSE,
      'clientSecret' => $paimentIndent['client_secret'],
      'payment_status' => "requires_payment_method",
      'return_url' => $request->getScheme() . '://' . $request->getHttpHost() . '/managepackvhsost/afterpay'
    ];
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
        '#name' => 'precedant',
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
  
  private function excuteCmd($cmd) {
    ob_start();
    $return_var = '';
    $output = '';
    exec($cmd . " 2>&1", $output, $return_var);
    $result = ob_get_contents();
    ob_end_clean();
    $debug = [
      'output' => $output,
      'return_var' => $return_var,
      'result' => $result,
      'script' => $cmd
    ];
    return $debug;
  }
  
}