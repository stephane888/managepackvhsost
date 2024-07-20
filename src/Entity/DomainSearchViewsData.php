<?php

namespace Drupal\managepackvhsost\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Domain search entities.
 */
class DomainSearchViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
