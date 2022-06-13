<?php

namespace Drupal\cart_sharing;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\PrivateKey;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;

/**
 * Default cart link token service implementation.
 */
class CartShareToken implements CartShareTokenInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The Drupal private key.
   *
   * @var \Drupal\Core\PrivateKey
   */
  protected $privateKey;

  /**
   * Constructs a new CartLinkToken object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\PrivateKey $private_key
   *   The Drupal private key.
   */
  public function __construct(AccountInterface $current_user, PrivateKey $private_key) {
    $this->currentUser = $current_user;
    $this->privateKey = $private_key;
  }

  /**
   * {@inheritdoc}
   */
  public function generate(int $cart_id) {

    $value = $this->generateTokenValue($cart_id);
    return substr(Crypt::hmacBase64($value, $this->privateKey->get() . $this->getHashSalt()), 0, 16);
  }

  /**
   * {@inheritdoc}
   */
  public function validate(int $cart_id, string $token) {
    $value = $this->generate($cart_id);
    return hash_equals($value, $token);
  }

  /**
   * Generates the value used for the token generation.
   *
   * @param int $cart_id
   *   Cart ID.
   *
   * @return string
   *   The value used for the token generation.
   */
  protected function generateTokenValue(int $cart_id) {
    return sprintf('share_cart:%s', $cart_id);
  }

  /**
   * Gets a salt useful for hardening against SQL injection.
   *
   * @return string
   *   A salt based on information in settings.php, not in the database.
   *
   * @throws \RuntimeException
   */
  protected function getHashSalt() {
    return Settings::getHashSalt();
  }

}
