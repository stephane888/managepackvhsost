<?php

namespace Drupal\managepackvhsost\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\managepackvhsost\Entity\DomainSearch;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Stephane888\Debug\ExceptionExtractMessage;

/**
 * Returns responses for managepackvhsost routes.
 */
class ManagepackvhsostController extends ControllerBase {
  protected $http_code;
  
  /**
   * Builds the response.
   */
  public function afterpay(Request $request) {
    $build = [];
    $redirect_status = $request->get('redirect_status');
    $payment_intent_client_secret = $request->get('payment_intent_client_secret');
    $payment_intent = $request->get('payment_intent');
    if ($redirect_status == 'succeeded') {
      $this->messenger()->addStatus("Paiement effectué avec succes.");
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
