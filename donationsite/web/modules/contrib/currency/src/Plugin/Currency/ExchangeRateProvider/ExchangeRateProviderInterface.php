<?php

namespace Drupal\currency\Plugin\Currency\ExchangeRateProvider;

use Commercie\CurrencyExchange\ExchangeRateProviderInterface as GenericExchangeRateProviderInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines a currency exchange rate provider plugin.
 */
interface ExchangeRateProviderInterface extends GenericExchangeRateProviderInterface, PluginInspectionInterface {

  /**
   * {@inheritdoc}
   *
   * @param string $sourceCurrencyCode
   * @param string $destinationCurrencyCode
   *
   * @return \Drupal\currency\ExchangeRateInterface|null
   */
  public function load($sourceCurrencyCode, $destinationCurrencyCode);

  /**
   * {@inheritdoc}
   *
   * @param array[] $currencyCodes
   *   Keys are the ISO 4217 codes of source currencies, values are arrays that
   *   contain ISO 4217 codes of destination currencies. Example:
   *   [
   *     'EUR' => ['NLG', 'DEM', 'XXX'],
   *   ]
   *
   * @return array[]
   *   Keys are the ISO 4217 codes of source currencies, values are arrays of
   *   which the keys are ISO 4217 codes of destination currencies and values
   *   are \Drupal\currency\ExchangeRateInterface objects, or NULL for
   *   combinations of currencies for which no exchange rate could be found.
   */
  public function loadMultiple(array $currencyCodes);

}
