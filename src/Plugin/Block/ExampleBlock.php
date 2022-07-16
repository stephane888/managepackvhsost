<?php

namespace Drupal\managepackvhsost\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "managepackvhsost_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("managepackvhsost")
 * )
 */
class ExampleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
