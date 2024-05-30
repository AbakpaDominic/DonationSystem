<?php

namespace Drupal\commerce_paystack\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface;

/**
 * Provides the interface for the Paystack payment gateway.
 */
interface PaystackStandardInterface extends OffsitePaymentGatewayInterface {

  /**
   * Get the Paystack API Secret key set for the payment gateway.
   *
   * @return string
   *   The Paystack API Secret key.
   */
  public function getSecretKey();
  
  /**
   * Verifies a previous transaction from the Paystack Standard payment gateway.
   *
   * @param $reference
   *
   * @return array
   */
  public function verifyTransaction($reference);
  
  /**
   * Returns a mapping of Paystack payment statuses to payment states.
   *
   * @param $status
   *   (optional) The Paystack payment status.
   *
   * @return array|string
   *   An array containing the Paystack remote statuses as well as their
   *   corresponding states. if $status is specified, the corresponding state
   *   is returned.
   */
  public function getStatusMapping($status = NULL);
  
}
