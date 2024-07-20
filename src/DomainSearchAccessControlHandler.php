<?php

namespace Drupal\managepackvhsost;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Domain search entity.
 *
 * @see \Drupal\managepackvhsost\Entity\DomainSearch.
 */
class DomainSearchAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\managepackvhsost\Entity\DomainSearchInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished domain search entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published domain search entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit domain search entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete domain search entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add domain search entities');
  }


}
