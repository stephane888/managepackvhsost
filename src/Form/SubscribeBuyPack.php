<?php

namespace Drupal\managepackvhsost\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\managepackvhsost\Services\CheckDomains;
use Drupal\ovh_api_rest\Services\ManageBuyDomain;
use Drupal\stripebyhabeuk\Services\PasserelleStripe;

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
    'site-pro' => 'Site Pro',
    'site-e-commerce' => 'Site e-Commerce'
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
    $form['#attributes']['class'][] = 'with-phone';
    $form['#attributes']['class'][] = 'mx-auto';
    $form['#attributes']['class'][] = 'width-phone';
    //
    
    $this->goToStepeDomain($form, $form_state);
    $this->getFormByStep($form, $form_state);
    $this->actionsButtons($form, $form_state);
    return $form;
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
      '#default_value' => isset($tempValue['type_pack']) ? $tempValue['type_pack'] : null,
      '#attributes' => [
        'class' => []
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
      '#required' => TRUE,
      '#default_value' => isset($tempValue['domaine']) ? $tempValue['domaine'] : $this->ManageBuyDomain->getDomain(),
      '#description' => 'Example de domaine : mini-garage.com, blogcuisine.fr ...'
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
        'p2y' => '2 ans <br> <span> 8€ * 24 mois </span>',
        'p1y' => '1 an <br> <span> 10€ * 12 mois </span> ',
        'p1m' => '1 mois <br> <span> 14 € </span>'
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
          'mb-5'
        ]
      ],
      
      [
        '#type' => 'html_tag',
        '#tag' => 'h4',
        '#value' => 'Payer votre commande '
      ],
      [
        '#type' => "html_tag",
        '#tag' => "strong",
        "#value" => "Total: " . $price . " €"
      ]
    ];
    $form['paiement-info-img'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => []
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
          return 120;
          break;
        case "p1m":
          return 14;
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
            'btn btn-secondary'
          ]
        ]
      ];
    if ($form_state->get('page_num') < self::$max_stape)
      $form['container_buttons']['next'] = [
        '#type' => 'submit',
        '#value' => 'Suivant',
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
            'w-auto'
          ],
          'data-trigger' => 'run'
        ]
      ];
    if ($form_state->get('page_num') == self::$max_stape) {
      $form['container_buttons']['actions'] = [
        '#type' => 'actions'
      ];
      $form['container_buttons']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t("Valider l'achat"),
        '#button_type' => 'secondary'
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
    if (isset($form['domaine'])) {
      $domaine = strtolower($form_state->getValue('domaine'));
      $form_state->setValue('domaine', $domaine);
      if (mb_strlen($domaine) < 3) {
        $form_state->setErrorByName('domaine', ' Nombre de caractere inssufisant ');
      }
      else {
        // Validation de domaine;
        $result = $this->ManageBuyDomain->searchDomain($domaine, $form_state->getValues());
        if (isset($result['status_domain']) && !$result['status_domain'])
          $form_state->setErrorByName('domaine', " Domaine non disponible, veillez nous contacter pour plus d'information ");
        else
          $this->ManageBuyDomain->saveDomain($domaine);
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
