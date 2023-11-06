<?php

namespace Drupal\managepackvhsost\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\managepackvhsost\Services\CheckDomains;
use Drupal\ovh_api_rest\Services\ManageBuyDomain;
use Drupal\stripebyhabeuk\Services\PasserelleStripe;
use Drupal\Component\Utility\NestedArray;
use Drupal\lesroidelareno\lesroidelareno;
use Drupal\Component\Utility\Html;
use Stephane888\Debug\Repositories\ConfigDrupal;

/**
 * Provides a managepackvhsost form.
 */
class SubscribeBuyPack extends FormBase {
  private static $max_stape = 4;
  
  /**
   *
   * @var \Drupal\managepackvhsost\Services\CheckDomains
   */
  protected $CheckDomains;
  
  /**
   *
   * @var \Drupal\ovh_api_rest\Services\ManageBuyDomain
   */
  protected $ManageBuyDomain;
  
  /**
   *
   * @var \Drupal\stripebyhabeuk\Services\PasserelleStripe
   */
  protected $PasserelleStripe;
  
  /**
   *
   * @var array
   */
  protected $type_packs = [
    'site-pro' => 'Pack standard'
    // 'site-e-commerce' => 'Site e-Commerce'
  ];
  
  /**
   *
   * @param CheckDomains $CheckDomains
   * @param ManageBuyDomain $ManageBuyDomain
   * @param PasserelleStripe $PasserelleStripe
   */
  function __construct(CheckDomains $CheckDomains, ManageBuyDomain $ManageBuyDomain, PasserelleStripe $PasserelleStripe) {
    $this->CheckDomains = $CheckDomains;
    $this->ManageBuyDomain = $ManageBuyDomain;
    $this->PasserelleStripe = $PasserelleStripe;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('managepackvhsost.search_domain'), $container->get('ovh_api_rest.manage_buy_domain'), $container->get('stripebyhabeuk.manage'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'managepackvhsost_subscribe_buy_pack';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['id'] = $this->getFormId();
    if (!$form_state->has('page_num') && empty($_GET['type_pack'])) {
      $class = [
        "padding-bottom",
        "container"
      ];
      $this->loadLayout($form);
    }
    else
      $class = [
        "padding-bottom",
        "padding-top",
        "width-phone",
        "mx-auto"
      ];
    $form['#attributes']['class'] = NestedArray::mergeDeepArray([
      $form['#attributes']['class'],
      $class
    ]);
    //
    
    $this->goToStepeDomain($form, $form_state);
    $this->getFormByStep($form, $form_state);
    $this->actionsButtons($form, $form_state);
    return $form;
  }
  
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
   */
  protected function getFormByStep(&$form, FormStateInterface $form_state) {
    if ($form_state->has('page_num')) {
      switch ($form_state->get('page_num')) {
        case '1':
          $this->form_stape_1($form, $form_state);
          break;
        case '2':
          $this->form_stape_2($form, $form_state);
          break;
        case '3':
          $this->form_stape_3($form, $form_state);
          break;
        case '4':
          $this->form_stape_4($form, $form_state);
          break;
        default:
          $this->messenger()->addWarning('Bad stepe');
          break;
      }
    }
    else {
      $form_state->set('page_num', 1);
      $this->form_stape_1($form, $form_state);
    }
  }
  
  /**
   * Selection du pack
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function form_stape_1(array &$form, FormStateInterface $form_state) {
    $n = $form_state->get('page_num');
    $tempValue = $form_state->get([
      'tempValues',
      $n
    ]);
    
    $form['type_pack'] = [
      '#type' => 'select',
      '#title' => $this->t('Selectionner un pack'),
      '#title_display' => false,
      '#required' => TRUE,
      '#default_value' => isset($tempValue['type_pack']) ? $tempValue['type_pack'] : 'site-pro',
      '#attributes' => [
        'class' => [
          'd-none'
        ]
      ],
      '#options' => $this->type_packs
    ];
  }
  
  /**
   * Selection du domaine
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function form_stape_2(array &$form, FormStateInterface $form_state) {
    $n = $form_state->get('page_num');
    $tempValue = $form_state->get([
      'tempValues',
      $n
    ]);
    
    $form['domaine'] = [
      '#type' => 'textfield',
      '#title' => $this->t(' Saisir un domaine pour votre site web '),
      // '#required' => TRUE, on doit mettre en place un validateur de domain.
      '#default_value' => isset($tempValue['domaine']) ? $tempValue['domaine'] : $this->ManageBuyDomain->getDomain(),
      '#description' => 'Example de domaine : mini-garage.com, blogcuisine.fr ...',
      '#states' => [
        'visible' => [
          ':input[name="domaine_exit"]' => [
            'checked' => false
          ],
          ':input[name="use_domaine_exit"]' => [
            'checked' => false
          ]
        ]
      ]
    ];
    $form['domaine_exit'] = [
      '#type' => 'checkbox',
      '#title' => $this->t(" J'ai déjà mon domaine "),
      '#required' => false,
      '#default_value' => isset($tempValue['domaine_exit']) ? $tempValue['domaine_exit'] : false,
      '#states' => [
        'visible' => [
          ':input[name="use_domaine_exit"]' => [
            'checked' => false
          ]
        ]
      ]
    ];
    $form['domaine_existing'] = [
      '#type' => 'textfield',
      '#title' => $this->t(' Votre domaine '),
      // '#required' => TRUE, on doit mettre en place un validateur de domain.
      '#default_value' => isset($tempValue['domaine_existing']) ? $tempValue['domaine_existing'] : '',
      '#description' => 'Le domaine est deja achété',
      '#states' => [
        'visible' => [
          ':input[name="domaine_exit"]' => [
            'checked' => true
          ]
        ]
      ]
    ];
    $ssdomain = lesroidelareno::getCurrentPrefixDomain() ? str_replace("_", "-", lesroidelareno::getCurrentPrefixDomain()) : '';
    $form['use_domaine_exit'] = [
      '#type' => 'checkbox',
      '#title' => $this->t(" Utiliser le sous domaine "),
      '#description' => $ssdomain . '.wb-horizon.com',
      '#required' => false,
      '#default_value' => isset($tempValue['use_domaine_exit']) ? $tempValue['use_domaine_exit'] : false,
      '#states' => [
        'visible' => [
          ':input[name="domaine_exit"]' => [
            'checked' => false
          ]
        ]
      ]
    ];
  }
  
  /**
   * Selection de la periode
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function form_stape_3(array &$form, FormStateInterface $form_state) {
    $n = $form_state->get('page_num');
    $tempValue = $form_state->get([
      'tempValues',
      $n
    ]);
    
    $form['periode'] = [
      '#type' => 'radios',
      '#title' => $this->t(' Cycles de facturation '),
      '#required' => TRUE,
      '#options' => [
        // 'p2y' => '2 ans <br> <span class="h5"> 8€ </span>',
        'p1m' => '1 mois <br> <span class="h2 m-0"> 39,99 € </span> <small>+ 1 mois gratuit</small>',
        'p1y' => '1 an <br> <span class="h2 m-0"> 399,9€  </span> <small>2 mois gratuit</small> '
      ],
      '#default_value' => isset($tempValue['periode']) ? $tempValue['periode'] : 'p1y'
    ];
  }
  
  /**
   * Paiement de la transaction
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function form_stape_4(array &$form, FormStateInterface $form_state) {
    $price = $this->getPrice($form, $form_state);
    $paimentIndent = $this->PasserelleStripe->paidInvoice($price);
    $config = ConfigDrupal::config('stripebyhabeuk.settings');
    $n = $form_state->get('page_num');
    $tempValue = $form_state->get([
      'tempValues',
      $n
    ]);
    $this->fieldsPaiement($form, $form_state);
    //
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
            'mb-4'
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
    $form['stripebyhabeuk_payment_method_id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'payment-method-id' . $idHtml
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
      'enable_credit_card_logos' => FALSE
    ];
    
    //
    $form['#attached']['library'][] = 'managepackvhsost/managepackvhsost';
    $form['#attached']['drupalSettings']['managepackvhsost']['client_secret'] = $paimentIndent['client_secret'];
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  function getPrice(array $form, FormStateInterface $form_state) {
    $tempValue = $form_state->get([
      'tempValues',
      3
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
    
    if ($n < 4)
      $form_state->set('page_num', $n + 1)->setRebuild(TRUE);
    else
      $form_state->set('page_num', $n)->setRebuild(TRUE);
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
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /**
     * Validation du domaine
     */
    if (!empty($form['domaine']) || !empty($form['domaine_existing'])) {
      $domaine = strtolower($form_state->getValue('domaine'));
      $form_state->setValue('domaine', $domaine);
      $oldDomain = strtolower($form_state->getValue('domaine_existing'));
      $form_state->setValue('domaine_existing', $oldDomain);
      $oldExit = $form_state->getValue('domaine_exit');
      // si le domaine exite deja.
      if ($oldExit) {
        if (mb_strlen($oldDomain) < 3) {
          $form_state->setErrorByName('domaine', ' Nombre de caractere inssufisant ');
        }
        else {
          // Validation de domaine;
          try {
          /**
           * Pour le moment on desactive cette etape, aucune consistance de la
           * garder.
           */
            // $result = $this->ManageBuyDomain->searchDomain($oldDomain,
            // $oldDomain);
            // if (isset($result['status_domain']) && $result['status_domain'])
            // $form_state->setErrorByName('domaine_existing', " Domaine non
            // disponible ");
            // else
            // $this->ManageBuyDomain->saveDomain($oldDomain);
          }
          catch (\Exception $e) {
            $form_state->setErrorByName('domaine_existing', $e->getMessage());
          }
        }
      }
      else {
        if (mb_strlen($domaine) < 3) {
          $form_state->setErrorByName('domaine', ' Nombre de caractere inssufisant ');
        }
        else {
          // Validation de domaine;
          try {
            $result = $this->ManageBuyDomain->searchDomain($domaine, $domaine);
            if (isset($result['status_domain']) && !$result['status_domain'])
              $form_state->setErrorByName('domaine', " Domaine non disponible, veillez nous contacter pour plus d'information ");
            else
              $this->ManageBuyDomain->saveDomain($domaine);
          }
          catch (\Exception $e) {
            $form_state->setErrorByName('domaine', $e->getMessage());
          }
        }
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
  
}
