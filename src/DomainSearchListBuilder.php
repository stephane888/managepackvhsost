<?php

namespace Drupal\managepackvhsost;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Domain search entities.
 *
 * @ingroup managepackvhsost
 */
class DomainSearchListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Domain search ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\managepackvhsost\Entity\DomainSearch $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.domain_search.edit_form',
      ['domain_search' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
