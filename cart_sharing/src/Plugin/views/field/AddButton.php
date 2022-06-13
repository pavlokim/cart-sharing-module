<?php

namespace Drupal\cart_sharing\Plugin\views\field;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\UncacheableFieldHandlerTrait;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Defines a form element for adding the product variation to the cart.
 *
 * @ViewsField("cart_sharing_add_button")
 */
class AddButton extends FieldPluginBase {

  use UncacheableFieldHandlerTrait;

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
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_order\Resolver\OrderTypeResolverInterface $order_type_resolver
   *   The order type resolver.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, OrderTypeResolverInterface $order_type_resolver, CurrentStoreInterface $current_store) {
   parent::__construct($configuration, $plugin_id, $plugin_definition);

   $this->cartManager = $cart_manager;
   $this->cartProvider = $cart_provider;
   $this->orderTypeResolver = $order_type_resolver;
   $this->currentStore = $current_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
   return new static(
     $configuration,
     $plugin_id,
     $plugin_definition,
     $container->get('commerce_cart.cart_manager'),
     $container->get('commerce_cart.cart_provider'),
     $container->get('commerce_order.chain_order_type_resolver'),
     $container->get('commerce_store.current_store')
   );
  }

  /**
   * {@inheritdoc}
   */
  public function clickSortable() {
   return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $row, $field = NULL) {
   return '<!--form-item-' . $this->options['id'] . '--' . $row->index . '-->';
  }

  /**
   * Form constructor for the views form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function viewsForm(array &$form, FormStateInterface $form_state) {
   // Make sure we do not accidentally cache this form.
   $form['#cache']['max-age'] = 0;
   // The view is empty, abort.
   if (empty($this->view->result)) {
     unset($form['actions']);
     return;
   }
   $form['actions']['submit']['#access'] = FALSE;

   foreach ($this->view->result as $row_index => $row) {
     $product_variation_id = $row->_entity->id();
     $form[$this->options['id']][$row_index] = [
       '#type' => 'link',
       '#title' => $this->t('Add to cart'),
       '#url' => Url::fromRoute('share.cart.add_to_cart_link',
         ['product_variation_id' => $product_variation_id],
         ['query' => [
           'token' => \Drupal::getContainer()->get('csrf_token')->get("add-cart/{$product_variation_id}"),
           'destination' => \Drupal::service('path.current')->getPath(),
         ]]),
       '#attributes' => ['class' => 'button'],
     ];
   }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
   // Do nothing.
  }

}
