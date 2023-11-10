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
  
  function __construct(GenerateDomainVhost $GenerateDomainVhost, PasserelleStripe $PasserelleStripe) {
    $this->GenerateDomainVhost = $GenerateDomainVhost;
    $this->PasserelleStripe = $PasserelleStripe;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('generate_domain_vps.vhosts'), $container->get('stripebyhabeuk.manage'));
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
  
}
