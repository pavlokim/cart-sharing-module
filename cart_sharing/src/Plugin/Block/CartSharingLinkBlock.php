<?php

namespace Drupal\cart_sharing\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides Cart sharing link block.
 *
 * @Block(
 *  id = "cart_sharing_link_block",
 *  admin_label = @Translation("Cart sharing link block"),
 *  context_definitions = {
 *    "url" = @ContextDefinition("string",
 *      label = @Translation("Cart sharing URL"),
 *    )
 *  }
 * )
 */
class CartSharingLinkBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $url = $this->getContext('url')->getContextValue();
    if (!empty($url)) {
      $build['#theme'] = 'cart_sharing_share_link_block';
      $build['#content'] = ['share_cart_link' => $url];
      $build['#attached']['library'][] = 'core/drupal.dialog.ajax';
    }

    return $build;
  }

}
