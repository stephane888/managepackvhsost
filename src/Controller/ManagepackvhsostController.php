<?php

namespace Drupal\managepackvhsost\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for managepackvhsost routes.
 */
class ManagepackvhsostController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
