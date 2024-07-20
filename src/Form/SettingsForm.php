<?php

namespace Drupal\managepackvhsost\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layoutgenentitystyles\Services\LayoutgenentitystylesServices;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure managepackvhsost settings for this site.
 */
class SettingsForm extends ConfigFormBase {
  
  /**
   *
   * @var LayoutgenentitystylesServices
   */
  protected $LayoutgenentitystylesServices;
  
  function __construct(LayoutgenentitystylesServices $LayoutgenentitystylesServices) {
    $this->LayoutgenentitystylesServices = $LayoutgenentitystylesServices;
  }
  
  static function create(ContainerInterface $container) {
    return new static($container->get('layoutgenentitystyles.add.style.theme'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'managepackvhsost_settings';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'managepackvhsost.settings'
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('managepackvhsost.settings');
    $form['api_apilayer_whois_api'] = [
      '#type' => 'textfield',
      '#title' => $this->t(' API apilayer whois api '),
      '#default_value' => $config->get('api_apilayer_whois_api')
    ];
    //
    $form['block_load_style_scss_js'] = [
      '#type' => 'textfield',
      '#title' => $this->t(' Load style scss & js '),
      '#default_value' => !empty($config->get('block_load_style_scss_js')) ? $config->get('block_load_style_scss_js') : 'managepackvhsost/managepackvhsost-settings'
    ];
    return parent::buildForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if ($form_state->getValue('example') != 'example') {
    // // $form_state->setErrorByName('example', $this->t('The value is not
    // // correct.'));
    // }
    parent::validateForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('managepackvhsost.settings');
    $block_load_style_scss_js = $form_state->getValue('block_load_style_scss_js');
    $config->set('api_apilayer_whois_api', $form_state->getValue('api_apilayer_whois_api'));
    $config->set('block_load_style_scss_js', $form_state->getValue('block_load_style_scss_js'));
    $config->save();
    //
    $this->LayoutgenentitystylesServices->addStyleFromModule($block_load_style_scss_js, 'managepackvhsost_settings', 'default');
    //
    parent::submitForm($form, $form_state);
  }
  
}
