<?php

namespace Drupal\managepackvhsost\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Domain search entities.
 *
 * @ingroup managepackvhsost
 */
interface DomainSearchInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Domain search name.
   *
   * @return string
   *   Name of the Domain search.
   */
  public function getName();

  /**
   * Sets the Domain search name.
   *
   * @param string $name
   *   The Domain search name.
   *
   * @return \Drupal\managepackvhsost\Entity\DomainSearchInterface
   *   The called Domain search entity.
   */
  public function setName($name);

  /**
   * Gets the Domain search creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Domain search.
   */
  public function getCreatedTime();

  /**
   * Sets the Domain search creation timestamp.
   *
   * @param int $timestamp
   *   The Domain search creation timestamp.
   *
   * @return \Drupal\managepackvhsost\Entity\DomainSearchInterface
   *   The called Domain search entity.
   */
  public function setCreatedTime($timestamp);

}
