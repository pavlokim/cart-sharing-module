<?php

namespace Drupal\cart_sharing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Render\RendererInterface;
use Drupal\cart_sharing\CartShareTokenInterface;
use Drupal\views\Views;

/**
 * Provides the cart page.
 */
class CartShareLinkController extends ControllerBase {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Token service.
   *
   * @var \Drupal\cart_sharing\CartShareTokenInterface
   */
  protected $token_service;

  /**
   * Constructs a new CartController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\cart_sharing\CartShareTokenInterface $token_service
   *   The token service.
   */
  public function __construct(RendererInterface $renderer, CartShareTokenInterface $token_service) {
    $this->renderer = $renderer;
    $this->token_service = $token_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('cart_sharing.token')
    );
  }

  /**
   * Access callback for the route.
   *
   * @param int $cart_id
   *   The cart ID.
   * @param string $token
   *   The token.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(int $cart_id, string $token) {

    return AccessResult::allowedIf($this->token_service->validate($cart_id, $token));
  }

  /**
   * Outputs a cart view for each non-empty cart belonging to the current user.
   *
   * @return array
   *   A render array.
   */
  public function getSharedCartPage($cart_id) {
    $build = [];

    $build['#markup'] = $this->getSharedCartView($cart_id);

    return $build;
  }

  /**
   * Gets the cart views for each cart.
   *
   * @param int $cart_id
   *   The sharable cart ID.
   *
   * @return object
   *   An array of view ids keyed by cart order ID.
   */
  protected function getSharedCartView(int $cart_id) {
    $view = Views::getView('cart_sharing_product_list');
    $rendered = $view->buildRenderable('default', [$cart_id]);

    return $this->renderer->render($rendered);
  }

}
