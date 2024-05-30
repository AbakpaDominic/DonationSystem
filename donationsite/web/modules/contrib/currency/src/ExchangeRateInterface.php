<?php

namespace Drupal\currency;

use Commercie\CurrencyExchange\ExchangeRateInterface as GenericExchangeRateInterface;

/**
 * Defines an exchange rate.
 *
 * Implementations may optionally implement any of the following interfaces:
 * - \Drupal\Core\Cache\CacheableDependencyInterface
 */
interface ExchangeRateInterface extends GenericExchangeRateInterface {

  /**
   * Gets the plugin ID of the exchange rate provider that provided this rate.
   *
   * @return string|null
   */
  public function getExchangeRateProviderId();

  /**
   * Sets the plugin ID of the exchange rate provider that provided this rate.
   *
   * @param string $id
   *
   * @return $this
   */
  public function setExchangeRateProviderId($id);

}
