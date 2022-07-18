<?php

namespace Drupal\managepackvhsost\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\managepackvhsost\Services\BlocksDomains;
use Drupal\layoutgenentitystyles\Services\LayoutgenentitystylesServices;

/**
 * Provides a display blocs domains block.
 *
 * @Block(
 *   id = "managepackvhsost_display_blocs_domains",
 *   admin_label = @Translation("Display blocs Domains"),
 *   category = @Translation("Custom")
 * )
 */
class DisplayBlocsDomainsBlock extends BlockBase implements ContainerFactoryPluginInterface {
  
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   * - BlocksDomains
   */
  protected $BlocksDomains;
  
  /**
   *
   * @var LayoutgenentitystylesServices
   */
  protected $LayoutgenentitystylesServices;
  
  /**
   * Constructs a new DisplayBlocsDomainsBlock instance.
   *
   * @param array $configuration
   *        The plugin configuration, i.e. an array with configuration values
   *        keyed
   *        by configuration option name. The special key 'context' may be used
   *        to
   *        initialize the defined contexts by setting it to an array of context
   *        values keyed by context names.
   * @param string $plugin_id
   *        The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *        The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *        The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, BlocksDomains $BlocksDomains, LayoutgenentitystylesServices $LayoutgenentitystylesServices) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->BlocksDomains = $BlocksDomains;
    $this->LayoutgenentitystylesServices = $LayoutgenentitystylesServices;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'), $container->get('managepackvhsost.blocksdomains'), $container->get('layoutgenentitystyles.add.style.theme'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'foo' => $this->t('Hello world!'),
      'block_load_style_scss_js' => 'managepackvhsost/managepackvhsost'
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['foo'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Foo'),
      '#default_value' => $this->configuration['foo']
    ];
    return $form;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['foo'] = $form_state->getValue('foo');
    $library = $this->configuration['block_load_style_scss_js'];
    $this->LayoutgenentitystylesServices->addStyleFromModule($library, 'managepackvhsost_display_blocs_domains', 'default');
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!')
    ];
    // return $build;
    $cts = $this->BlocksDomains->getRenders();
    return $cts;
  }
  
}
