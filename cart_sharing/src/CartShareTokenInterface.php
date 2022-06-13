<?php

namespace Drupal\cart_sharing;

/**
 * Provides the cart link token interface.
 *
 * This service is responsible for both generating and validating tokens that
 * are added to the cart links.
 */
interface CartShareTokenInterface {

  /**
   * Generates a token cart share link.
   *
   * The token is added to the add to cart link.
   *
   * @param int $cart_id
   *   The cart id.
   *
   * @return string
   *   The generated token.
   */
  public function generate(int $cart_id);

  /**
   * Checks the given token.
   *
   * @param int $cart_id
   *   The cart id.
   * @param string $token
   *   The token to be validated.
   *
   * @return bool
   *   TRUE, if the given token is valid, FALSE otherwise.
   */
  public function validate(int $cart_id, string $token);

}
