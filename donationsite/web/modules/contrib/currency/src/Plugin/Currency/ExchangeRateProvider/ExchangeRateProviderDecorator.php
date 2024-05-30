<?php

namespace Drupal\currency\Plugin\Currency\ExchangeRateProvider;

use Commercie\CurrencyExchange\ExchangeRateProviderInterface as GenericExchangeRateProviderInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\currency\ExchangeRate;

/**
 * Provides an exchange rate provider decorator.
 */
class ExchangeRateProviderDecorator extends PluginBase implements ExchangeRateProviderInterface {

  /**
   * The decorated exchange rate provider
   *
   * @var \Commercie\CurrencyExchange\ExchangeRateProviderInterface
   */
  protected $exchangeRateProvider;

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Commercie\CurrencyExchange\ExchangeRateProviderInterface
   *   The decorated exchange rate provider.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GenericExchangeRateProviderInterface $exchange_rate_provider) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->exchangeRateProvider = $exchange_rate_provider;
  }

  /**
   * {@inheritdoc}
   */
  public function load($source_currency_code, $destination_currency_code) {
    $exchange_rate = $this->exchangeRateProvider->load($source_currency_code, $destination_currency_code);

    return ExchangeRate::createFromExchangeRate($exchange_rate, $this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $currency_codes) {
    $exchange_rates = [];
    foreach ($this->exchangeRateProvider->loadMultiple($currency_codes) as $source_currency_code => $destinations) {
      foreach ($destinations as $destination_currency_code => $exchange_rate) {
        $exchange_rates[$source_currency_code][$destination_currency_code] = ExchangeRate::createFromExchangeRate($exchange_rate, $this->getPluginId());
      }
    }

    return $exchange_rates;
  }
}
