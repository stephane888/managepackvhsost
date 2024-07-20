<?php

namespace Drupal\managepackvhsost\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Domain search entity.
 *
 * @ingroup managepackvhsost
 *
 * @ContentEntityType(
 *   id = "domain_search",
 *   label = @Translation("Domain search"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\managepackvhsost\DomainSearchListBuilder",
 *     "views_data" = "Drupal\managepackvhsost\Entity\DomainSearchViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\managepackvhsost\Form\DomainSearchForm",
 *       "add" = "Drupal\managepackvhsost\Form\DomainSearchForm",
 *       "edit" = "Drupal\managepackvhsost\Form\DomainSearchForm",
 *       "delete" = "Drupal\managepackvhsost\Form\DomainSearchDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\managepackvhsost\DomainSearchHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\managepackvhsost\DomainSearchAccessControlHandler",
 *   },
 *   base_table = "domain_search",
 *   translatable = FALSE,
 *   admin_permission = "administer domain search entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/domain_search/{domain_search}",
 *     "add-form" = "/admin/structure/domain_search/add",
 *     "edit-form" = "/admin/structure/domain_search/{domain_search}/edit",
 *     "delete-form" = "/admin/structure/domain_search/{domain_search}/delete",
 *     "collection" = "/admin/structure/domain_search",
 *   },
 *   field_ui_base_route = "domain_search.settings"
 * )
 */
class DomainSearch extends ContentEntityBase implements DomainSearchInterface {
  
  use EntityChangedTrait;
  use EntityPublishedTrait;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id()
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the Domain search entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'author',
      'weight' => 0
    ])->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t('Name'))->setDescription(t('The name of the Domain search entity.'))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    
    $fields['domain_external'] = BaseFieldDefinition::create('string')->setLabel(t("domain externe"))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setConstraints([
      'UniqueField' => []
    ]);
    
    $fields['domain_id_drupal'] = BaseFieldDefinition::create('entity_reference')->setLabel(t(' Domaine ID from drupal '))->setSetting('target_type', 'domain')->setSetting('handler', 'default')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['cycle_facturation'] = BaseFieldDefinition::create('string')->setLabel(t("cycle facturation"))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setConstraints([
      'UniqueField' => []
    ]);
    
    $fields['client_secret'] = BaseFieldDefinition::create('string')->setLabel(t("client secret"))->setSettings([
      'max_length' => 250,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setConstraints([
      'UniqueField' => []
    ]);
    
    $fields['is_paid'] = BaseFieldDefinition::create('boolean')->setLabel(" payé ? ")->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => 3
    ])->setDisplayOptions('view', [])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setDefaultValue(false);
    
    $fields['abonnement'] = BaseFieldDefinition::create('list_string')->setLabel(" type abonnement ")->setDisplayOptions('form', [
      'type' => 'options_buttons',
      'weight' => 5,
      'settings' => array(
        'match_operator' => 'CONTAINS',
        'size' => '10',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      )
    ])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setSettings([
      'allowed_values' => [
        'site-pro' => 'Pack standard',
        'site-vip' => 'Pack VIP'
      ]
    ])->setRequired(true)->setDefaultValue('new');
    
    $fields['status']->setDescription(t('A boolean indicating whether the Domain search is published.'))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));
    
    return $fields;
  }
  
}
