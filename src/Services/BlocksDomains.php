<?php

namespace Drupal\managepackvhsost\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Database\Connection;
use Drupal\domain\Entity\Domain;

class BlocksDomains {
  
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $user;
  
  /**
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;
  
  /**
   *
   * @var [EntityTypeManagerInterface]
   */
  protected $blocks = [];
  
  function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxy $user, Connection $Connection) {
    $this->entityTypeManager = $entity_type_manager;
    $this->user = $user;
    $this->connection = $Connection;
  }
  
  /**
   * -
   */
  public function getRenders() {
    $this->getdatas();
    $domaines = [
      'romx' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => [
            'row this-fock'
          ]
        ]
      ]
    ];
    
    foreach ($this->blocks as $block) {
      // dump($block);
      $domaines['romx'][] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => [
            'col-md-6',
            'col-sm-12'
          ]
        ],
        $block
      ];
    }
    
    //
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [],
      $domaines
    ];
  }
  
  /**
   *
   * @return [EntityTypeManagerInterface]
   */
  protected function getdatas() {
    $user_id = $this->user->id();
    $query = $this->entityTypeManager->getStorage('domain_ovh_entity')->getQuery();
    $query->condition('user_id', $user_id);
    $query->pager(6);
    $ids = $query->execute();
    
    if (!empty($ids)) {
      $entities = $this->entityTypeManager->getStorage('domain_ovh_entity')->loadMultiple($ids);
      foreach ($entities as $value) {
        
        // Load entity : donnee_internet_entity
        $donnee_internet_entity = $this->entityTypeManager->getStorage('donnee_internet_entity')->loadByProperties([
          'domain_ovh_entity' => $value->id(),
          'user_id' => $user_id
        ]);
        if (!empty($donnee_internet_entity)) {
          $donnee_internet_entity = reset($donnee_internet_entity);
        }
        
        // Load entity : domain
        $domain = $this->entityTypeManager->getStorage('domain')->loadByProperties([
          'id' => $value->get('domain_id_drupal')->target_id
        ]);
        $domaines['list-domaine'] = [
          '#type' => 'html_tag',
          '#tag' => 'ul',
          '#attributes' => [
            'class' => [
              'puce-check'
            ]
          ]
        ];
        if (!empty($domain)) {
          /**
           *
           * @var Domain $domain
           */
          $domain = reset($domain);
          $this->getDomaines($domain, $domaines['list-domaine']);
        }
        
        $this->blocks[] = [
          'content' => [
            '#theme' => 'managepackvhsost_blocks',
            '#image' => !empty($domain) ? $domain->getPath() : null,
            '#name_site' => !empty($donnee_internet_entity) ? $donnee_internet_entity->label() . ' <small class="text-muted" >(' . $donnee_internet_entity->id() . ')</small> ' : '',
            '#date' => !empty($donnee_internet_entity) ? \Drupal::service('date.formatter')->format($donnee_internet_entity->get('created')->value) : '',
            '#domaines' => $domaines,
            '#souscription' => $this->getSouscription($domain),
            '#change_domain' => 'change_domain',
            '#dissociate_domain' => 'dissociate_domain'
          ]
        ];
      }
    }
    return $this->blocks;
  }
  
  protected function getSouscription($domain) {
    return [
      [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => [
            'text-souscription'
          ]
        ],
        '#value' => ' Souscrire à un pack'
      ],
      [
        \Drupal::formBuilder()->getForm('Drupal\managepackvhsost\Form\AddPublicDomainOnSubDomain', $domain)
      ]
    ];
  }
  
  protected function getDomaines(Domain $domain, array &$reult) {
    if (!empty($domain))
      $reult[] = [
        '#type' => 'html_tag',
        '#tag' => 'li',
        '#attributes' => [
          'class' => []
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#attributes' => [
            'href' => $domain->getPath(),
            'target' => 'blank'
          ],
          '#value' => $domain->getHostname()
        ]
      ];
  }
  
}