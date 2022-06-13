<?php

namespace Drupal\cart_sharing\Controller;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides the cart page.
 */
class CartShareAddToCartController extends ControllerBase {

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The order type resolver.
   *
   * @var \Drupal\commerce_order\Resolver\OrderTypeResolverInterface
   */
  protected $orderTypeResolver;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

  /**
   * Constructs a new ProductVariationAddToCart object.
   *
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_order\Resolver\OrderTypeResolverInterface $order_type_resolver
   *   The order type resolver.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   */
  public function __construct(CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, OrderTypeResolverInterface $order_type_resolver, CurrentStoreInterface $current_store) {
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->orderTypeResolver = $order_type_resolver;
    $this->currentStore = $current_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_order.chain_order_type_resolver'),
      $container->get('commerce_store.current_store')
    );
  }

  /**
   * Adds the variant to the cart.
   *
   * @param int $product_variation_id
   *   The entity being purchased.
   */
  public function addToCart($product_variation_id) {

    $entityTypeManager = \Drupal::service('entity_type.manager');
    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $product_variation */
    $product_variation = $entityTypeManager->getStorage('commerce_product_variation')->load($product_variation_id);

    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $entityTypeManager->getStorage('commerce_order_item');
    $order_item = $order_item_storage->createFromPurchasableEntity($product_variation);

    $order_type_id = $this->orderTypeResolver->resolve($order_item);

    $store = $this->selectStore($product_variation);

    $cart = $this->cartProvider->getCart($order_type_id, $store);

    if (!$cart) {
      $cart = $this->cartProvider->createCart($order_type_id, $store);
    }

    $this->cartManager->addEntity($cart, $product_variation);

    $destination = \Drupal::request()->query->get('destination');

    if ($destination) {
      return new RedirectResponse($destination);
    }

    return new RedirectResponse('/');
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

}
