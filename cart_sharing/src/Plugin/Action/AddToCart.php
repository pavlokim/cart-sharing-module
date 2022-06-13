<?php

namespace Drupal\cart_sharing\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Add to cart product variation.
 *
 * @Action(
 *   id = "add_to_cart",
 *   label = @Translation("Add product varioation to cart"),
 *   type = "commerce_product_variation"
 * )
 */
class AddToCart extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {

    /** @var \Drupal\commerce_product\Entity\ProductInterface $object */
    $result = $object
      ->access('view', $account, TRUE);

    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $this->addProductToCart($entity);
  }

  /**
   * Selects the store for the given purchasable entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The entity being added to cart.
   *
   * @return \Drupal\commerce_store\Entity\StoreInterface
   *   The selected store.
   */
  protected function selectStore(PurchasableEntityInterface $entity) {
    $stores = $entity->getStores();
    if (count($stores) === 1) {
      $store = reset($stores);
    }
    else {
      $store = $this->currentStore->getStore();
      if (!in_array($store, $stores)) {
        throw new \Exception("The product can't be sold from current store..");
      }
    }

    return $store;
  }

  /**
   * Adds the variant to the cart.
   *
   * @param Drupal\commerce_product\Entity\ProductVariation $purchased_entity
   *   The entity being purchased.
   */
  protected function addProductToCart(ProductVariation $purchased_entity) {
    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $order_item_storage = $entityTypeManager->getStorage('commerce_order_item');
    $order_item = $order_item_storage->createFromPurchasableEntity($purchased_entity);

    $order_type_id = \Drupal::service('commerce_order.chain_order_type_resolver')->resolve($order_item);

    $store = $this->selectStore($purchased_entity);

    $cart = \Drupal::service('commerce_cart.cart_provider')->getCart($order_type_id, $store);

    if (!$cart) {
      $cart = \Drupal::service('commerce_cart.cart_provider')->createCart($order_type_id, $store);
    }

    \Drupal::service('commerce_cart.cart_manager')->addEntity($cart, $purchased_entity);
  }

}
