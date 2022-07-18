<?php

namespace Drupal\managepackvhsost\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a managepackvhsost form.
 */
class SubscribeBuyPack extends FormBase {
  private static $max_stape = 4;
  
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
    $this->getFormByStep($form, $form_state);
    $this->actionsButtons($form, $form_state);
    $this->messenger()->addStatus($form_state->getValue('type_pack', 'vide'), true);
    return $form;
  }
  
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
        'class' => [
          'form-control-sm'
        ]
      ],
      '#options' => [
        'site-pro' => 'Site Pro',
        'site-e-commerce' => 'Site e-Commerce'
      ]
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
      '#title' => $this->t('Domaine'),
      '#required' => TRUE,
      '#default_value' => isset($tempValue['domaine']) ? $tempValue['domaine'] : null
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
      '#type' => 'textfield',
      '#title' => $this->t('Periode'),
      '#required' => TRUE,
      '#default_value' => isset($tempValue['periode']) ? $tempValue['periode'] : null
    ];
  }
  
  /**
   * paiement de la transaction
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function form_stape_4(array &$form, FormStateInterface $form_state) {
    $n = $form_state->get('page_num');
    $tempValue = $form_state->get([
      'tempValues',
      $n
    ]);
    $form['paiement'] = [
      '#type' => 'checkbox',
      '#title' => $this->t(' payer votre commande '),
      '#default_value' => isset($tempValue['periode']) ? $tempValue['periode'] : ''
    ];
  }
  
  protected function actionsButtons(array &$form, FormStateInterface $form_state) {
    $form['container_buttons'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'd-flex',
          'justify-content-around',
          'align-items-center',
          'step-donneesite--submit'
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
          'effect' => 'fade'
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
        '#value' => $this->t('Send')
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
  
  public function selectPreviewsCallback(array $form, FormStateInterface $form_state) {
    return $form;
  }
  
  public function selectNextCallback(array $form, FormStateInterface $form_state) {
    return $form;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if (mb_strlen($form_state->getValue('message')) < 10) {
    // $form_state->setErrorByName('name', $this->t('Message should be at least
    // 10 characters.'));
    // }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }
  
}
