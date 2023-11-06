<?php

namespace Drupal\managepackvhsost\Plugin\Layout\Sections;

use Drupal\bootstrap_styles\StylesGroup\StylesGroupManager;
use Drupal\formatage_models\Plugin\Layout\Sections\FormatageModelsSection;

/**
 * A very advanced custom layout.
 *
 * @Layout(
 *   id = "managepackvhsost_static_pricing",
 *   label = @Translation(" Managepackvhsost static pricing "),
 *   category = @Translation("managepackvhsost"),
 *   path = "layouts/sections",
 *   template = "managepackvhsost-static-pricing",
 *   library = "managepackvhsost/managepackvhsost-static-pricing",
 *   default_region = "title",
 *   regions = {
 *     "title" = {
 *       "label" = @Translation("title"),
 *     },
 *     "body" = {
 *       "label" = @Translation("Body"),
 *     }
 *   }
 * )
 */
class StaticPricing extends FormatageModelsSection {
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\formatage_models\Plugin\Layout\FormatageModels::__construct()
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StylesGroupManager $styles_group_manager) {
    // TODO Auto-generated method stub
    parent::__construct($configuration, $plugin_id, $plugin_definition, $styles_group_manager);
    $this->pluginDefinition->set('icon', $this->pathResolver->getPath('module', 'formatage_models') . "/icones/formatage-models-comments.png");
  }
  
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'css' => '',
      'region_css_entete' => "col-md-6 ml-auto",
      'sf' => [
        'builder-form' => true,
        'info' => [
          'title' => 'Contenu',
          'loader' => 'dynamique'
        ],
        'fields' => [
          'title' => [
            'text_html' => [
              'label' => 'Entete',
              'value' => "Avis clients",
              "format" => "basic_html"
            ]
          ],
          'body' => [
            'text_html' => [
              'label' => 'titre',
              'value' => "Clients says",
              "format" => "basic_html"
            ]
          ]
        ]
      ]
    ];
  }
  
}
