<?php

namespace Drupal\managepackvhsost\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\managepackvhsost\Entity\DomainSearch;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Stephane888\Debug\ExceptionExtractMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\generate_domain_vps\Services\GenerateDomainVhost;
use Drupal\stripebyhabeuk\Services\PasserelleStripe;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Drupal\commerce_price\Price;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Url;
use Symfony\Component\Mime\Header\MailboxHeader;
use Symfony\Component\Mime\Address;

/**
 * Returns responses for managepackvhsost routes.
 */
class ManagepackvhsostController extends ControllerBase {
  protected $http_code;
  
  /**
   *
   * @var \Drupal\generate_domain_vps\Services\GenerateDomainVhost
   */
  protected $GenerateDomainVhost;
  
  /**
   *
   * @var \Drupal\stripebyhabeuk\Services\PasserelleStripe
   */
  protected $PasserelleStripe;
  
  /**
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $CacheRender;
  
  function __construct(GenerateDomainVhost $GenerateDomainVhost, PasserelleStripe $PasserelleStripe, CacheBackendInterface $CacheRender) {
    $this->GenerateDomainVhost = $GenerateDomainVhost;
    $this->PasserelleStripe = $PasserelleStripe;
    $this->CacheRender = $CacheRender;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('generate_domain_vps.vhosts'), $container->get('stripebyhabeuk.manage'), $container->get('cache.render'));
  }
  
  /**
   * Builds the response.
   */
  public function afterpay(Request $request) {
    $uid = \Drupal::currentUser()->id();
    $build = [];
    $redirect_status = $request->get('redirect_status');
    $payment_intent_client_secret = $request->get('payment_intent_client_secret');
    $payment_intent_id = $request->get('payment_intent');
    if ($redirect_status == 'succeeded') {
      $payment_intent = $this->PasserelleStripe->getPaymentIndent($payment_intent_id);
      // dd($payment_intent->currency);
      $this->messenger()->addStatus("Paiement effectué avec succes.");
      $query = $this->entityTypeManager()->getStorage('domain_search')->getQuery();
      $query->condition("user_id", $uid);
      $query->condition('client_secret', $payment_intent_client_secret);
      $query->accessCheck(TRUE);
      $ids = $query->execute();
      $id = reset($ids);
      $domain_search = \Drupal\managepackvhsost\Entity\DomainSearch::load($id);
      $domain_search->set('status', true);
      $domain_search->set('is_paid', true);
      // On enregistre chaque paiement
      $query_commerce_payment = $this->entityTypeManager()->getStorage('commerce_payment')->getQuery();
      $query_commerce_payment->accessCheck(TRUE);
      $query_commerce_payment->condition('remote_id', $payment_intent_id);
      $ids = $query_commerce_payment->execute();
      if (!$ids) {
        $amount = new Price($payment_intent->amount / 100, 'EUR');
        $values = [
          'payment_gateway' => 'commander',
          'remote_id' => $payment_intent_id,
          'remote_state' => $redirect_status,
          'state' => 'complete'
        ];
        $payment = \Drupal\commerce_payment\Entity\Payment::create($values);
        $payment->setAmount($amount);
        $payment->save();
      }
      // On genere le fichier host si necessaire.
      $domain_external = $domain_search->get('domain_external')->value;
      if ($domain_external) {
        $this->messenger()->addStatus(" Votre domaine nouveau domaine a été parfaitement configurer et serra disponible dans environ 10 minutes. ");
        $id = \preg_replace('/[^a-z0-9_]+/', "_", $domain_external);
        // genere le nouveau certificat.
        $this->GenerateDomainVhost->generateSSLForDomainAndCreatedomainOnVps($domain_external);
        // On ajoute l'alias.
        $domainAlias = $this->entityTypeManager()->getStorage('domain_alias')->load($id);
        if (!$domainAlias) {
          $values = [
            'id' => $id,
            'domain_id' => $domain_search->get('domain_id_drupal')->target_id,
            'label' => $domain_external,
            'environment' => 'local',
            'redirect' => '0',
            'pattern' => $domain_external
          ];
          $domainAlias = \Drupal\domain_alias\Entity\DomainAlias::create($values);
          $domainAlias->save();
        }
        else {
          $domainAlias->set('label', $domain_external);
          $domainAlias->set('pattern', $domain_external);
          $domainAlias->set('domain_id', $domain_search->get('domain_id_drupal')->target_id);
          $domainAlias->save();
          // envoit de mail :
          $to = \Drupal::currentUser()->getEmail();
          $name = \Drupal::currentUser()->getAccountName();
          $subject = "Souscription à un abonnement";
          $message = "
Bonjour $name,
Nous vous remercions pour votre souscription.

Cordialement l'equiepe wb-horizon
";
          $this->sendMails($to, $subject, $message);
          drupal_flush_all_caches();
          // $this->CacheRender->invalidateAll();
          // //
          // /**
          // *
          // * @var \Drupal\Core\Cache\CacheBackendInterface $CacheEntry
          // */
          // $CacheEntry = \Drupal::service('cache.entity');
          // $CacheEntry->invalidateAll();
          // /**
          // *
          // * @var \Drupal\Core\Cache\CacheBackendInterface $Cachestatic
          // */
          // $Cachestatic = \Drupal::service('cache.static');
          // $Cachestatic->invalidateAll();
          // /**
          // *
          // * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
          // $page_cache_kill_switch
          // */
          // $page_cache_kill_switch =
          // \Drupal::service('page_cache_kill_switch');
          // $page_cache_kill_switch->trigger();
        }
      }
      
      // on redirige l'utilisateur vers sa page.
      return $this->redirect('user.page');
    }
    else {
      $this->messenger()->addError("Une erreur s'est produite");
      $debug = "Error de paiement ; " . $redirect_status . " || " . $payment_intent_client_secret . " || " . $payment_intent;
      $this->getLogger('managepackvhsost')->critical($debug);
    }
    
    return $build;
  }
  
  /**
   *
   * @param string $search
   * @return string[]|\Drupal\Core\StringTranslation\TranslatableMarkup[]
   */
  public function saveSearch($search) {
    // $values = [
    // 'name' => $search
    // ];
    // $DomainSearch = DomainSearch::create($values);
    // $DomainSearch->save();
    // $this->curlApi($search);
    $configs = [
      $search
    ];
    return $this->reponse($configs);
  }
  
  public function domain_aliasCollection() {
    $build = [];
    $query = $this->entityTypeManager()->getStorage('domain_alias')->getQuery();
    $query->accessCheck(TRUE);
    $query->sort('domain_id');
    $query->pager(50);
    $ids = $query->execute();
    $header = [
      'id' => '#id',
      'name' => 'Titre',
      'redirect' => 'redirect',
      'environment' => 'environment',
      'pattern' => 'pattern',
      'domain_id' => 'domain_id',
      'operations' => 'operations'
    ];
    $rows = [];
    if ($ids) {
      $entities = $this->entityTypeManager()->getStorage('domain_alias')->loadMultiple($ids);
      foreach ($entities as $entity) {
        /**
         *
         * @var \Drupal\domain_alias\Entity\DomainAlias $entity
         */
        $id = $entity->id();
        $rows[$id] = [
          'id' => $id,
          'name' => $entity->hasLinkTemplate('canonical') ? [
            'data' => [
              '#type' => 'link',
              '#title' => $entity->label(),
              '#weight' => 10,
              '#url' => $entity->toUrl('canonical')
            ]
          ] : $entity->label(),
          'redirect' => $entity->getRedirect(),
          'environment' => $entity->getEnvironment(),
          'pattern' => $entity->getPattern(),
          'domain_id' => [
            'data' => [
              '#type' => 'link',
              '#title' => $entity->getDomainId(),
              '#weight' => 10,
              '#url' => Url::fromRoute('entity.domain.edit_form', [
                'domain' => $entity->getDomainId()
              ])
            ]
          ],
          'operations' => [
            'data' => $this->buildOperations($entity)
          ]
        ];
      }
    }
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#title' => 'Titre de la table',
      '#rows' => $rows,
      '#empty' => 'Aucun contenu',
      '#attributes' => [
        'class' => [
          'page-content00'
        ]
      ]
    ];
    $build['pager'] = [
      '#type' => 'pager'
    ];
    return $build;
  }
  
  /**
   * Builds a renderable list of operation links for the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *        The entity on which the linked operations will be performed.
   *        
   * @return array A renderable array of operation links.
   *        
   * @see \Drupal\Core\Entity\EntityListBuilder::buildRow()
   */
  public function buildOperations($entity) {
    $build = [
      '#type' => 'operations',
      '#links' => $this->getOperations($entity)
    ];
    
    return $build;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOperations($entity) {
    $operations = [];
    if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $entity->toUrl('edit-form')
      ];
    }
    if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => $entity->toUrl('delete-form')
      ];
    }
    return $operations;
  }
  
  /**
   *
   * @param array|string $configs
   * @param number $code
   * @param string $message
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function reponse($configs, $code = null, $message = null) {
    if (!is_string($configs))
      $configs = Json::encode($configs);
    $reponse = new JsonResponse();
    if ($code)
      $reponse->setStatusCode($code, $message);
    $reponse->setContent($configs);
    return $reponse;
  }
  
  protected function sendMails(string $to, string $subject, $message, $from = null) {
    $siteInfo = ConfigDrupal::config('system.site');
    $mailSystem = ConfigDrupal::config('mailsystem.settings');
    if (!$from) {
      $from = $siteInfo['mail'];
    }
    $key = 'booking_system_mail';
    $datas = [
      'id' => $key,
      'to' => $to,
      'subject' => $subject,
      'body' => $message,
      'headers' => [
        'From' => $from,
        'Sender' => $from,
        'Return-Path' => $from
      ]
    ];
    /**
     * On initialise le chargeur de plugin de mail.
     *
     * @var \Drupal\Core\Mail\MailManager $PluginMailManger
     */
    $PluginMailManger = \Drupal::service('plugin.manager.mail');
    
    /**
     * On recupere l'instance à partir de l'id du plugin.
     *
     * @var \Drupal\Core\Mail\MailInterface $mailPlugin
     */
    $mailPlugin = $PluginMailManger->createInstance($mailSystem['defaults']['sender']);
    
    $mailbox = new MailboxHeader('From', new Address($siteInfo['mail'], $siteInfo['name']));
    $datas['headers']['From'] = $mailbox->getBodyAsString();
    $message = $mailPlugin->format($datas);
    $result = $mailPlugin->mail($message);
    if (!$result) {
      $message = t(' There was a problem sending your email notification to @email. ', array(
        '@email' => $to
      ));
      $this->getLogger('managepackvhsost')->alert($message);
    }
  }
  
}
