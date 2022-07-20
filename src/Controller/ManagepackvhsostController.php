<?php

namespace Drupal\managepackvhsost\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\managepackvhsost\Entity\DomainSearch;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for managepackvhsost routes.
 */
class ManagepackvhsostController extends ControllerBase {
  protected $http_code;
  
  /**
   * Builds the response.
   */
  public function build() {
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!')
    ];
    
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
