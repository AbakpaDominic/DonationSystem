<?php

namespace Drupal\currency;

use Commercie\CurrencyExchange\ExchangeRate as GenericExchangeRate;
use Commercie\CurrencyExchange\ExchangeRateInterface as GenericExchangeRateInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

/**
 * Provides an exchange rate.
 */
class ExchangeRate extends GenericExchangeRate implements ExchangeRateInterface, RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The ID of the exchange rate provider that provided this rate.
   *
   * @return string|null
   */
  protected $exchangeRateProviderId;

  /**
   * Constructs a new instance.
   *
   * @param string $source_currency_code
   *   The code of the source currency.
   * @param string $destination_currency_code
   *   The code of the destination currency.
   * @param string $rate
   *   The exchange rate.
   * @param string $exchange_rate_provider_id
   *   The ID of the exchange rate provider that provided this rate.
   */
  public function __construct($source_currency_code, $destination_currency_code, $rate, $exchange_rate_provider_id) {
    parent::__construct($source_currency_code, $destination_currency_code, $rate);
    $this->exchangeRateProviderId = $exchange_rate_provider_id;
  }

  /**
   * Creates a new exchange rate based on another one.
   *
   * @param \Commercie\CurrencyExchange\ExchangerateInterface $other_exchange_rate
   *   The code of the source currency.
   * @param string $exchange_rate_provider_id
   *   The ID of the exchange rate provider that provided this rate.
   *
   * @return static
   */
  public static function createFromExchangeRate(GenericExchangerateInterface $other_exchange_rate, $exchange_rate_provider_id) {
    $exchange_rate = new static($other_exchange_rate->getSourceCurrencyCode(), $other_exchange_rate->getDestinationCurrencyCode(), $other_exchange_rate->getRate(), $exchange_rate_provider_id);
    $exchange_rate->setTimestamp($other_exchange_rate->getTimestamp());

    return $exchange_rate;
  }

  /**
   * {@inheritdoc}
   */
  public function getExchangeRateProviderId() {
    return $this->exchangeRateProviderId;
  }

  /**
   * {@inheritdoc}
   */
  public function setExchangeRateProviderId($id) {
    $this->exchangeRateProviderId = $id;

    return $this;
  }

}
