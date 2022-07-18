<?php

namespace Drupal\managepackvhsost\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a managepackvhsost form.
 */
class AddPublicDomainOnSubDomain extends FormBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'managepackvhsost_add_public_domain_on_sub_domain';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $domain = null) {
    $form['type_pack'] = [
      '#type' => 'select',
      '#title' => $this->t('Selectionner un pack'),
      '#title_display' => false,
      '#required' => TRUE,
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
    $form['domain_id'] = [
      '#type' => 'hidden',
      '#value' => !empty($domain) ? $domain->id() : ''
    ];
    $form['actions'] = [
      '#type' => 'actions'
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Subscribe'),
      '#attributes' => [
        'class' => [
          'btn-sm',
          'form-control-sm'
        ]
      ]
    ];
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
    $this->messenger()->addStatus($this->t(' The message has been sent. '));
    // $form_state->setRedirect('<front>');
    $route_parameters = [];
    $options = [
      'query' => [
        'type_pack' => $form_state->getValue('type_pack'),
        'domain_id' => $form_state->getValue('domain_id')
      ]
    ];
    $form_state->setRedirect('managepackvhsost.subscribe_buy_pack', $route_parameters, $options);
  }
  
}
