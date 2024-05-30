<?php

namespace Drupal\currency;

/**
 * Defines a config importer.
 */
interface ConfigImporterInterface {

  /**
   * Gets all currencies that can be imported.
   *
   * @return \Drupal\currency\Entity\CurrencyInterface[]
   */
  public function getImportableCurrencies();

  /**
   * Imports a currency.
   *
   * @param string $currency_code
   *
   * @return \Drupal\currency\Entity\CurrencyInterface|false
   *   The imported currency or FALSE in case of errors.
   */
  public function importCurrency($currency_code);

  /**
   * Gets all currency locales that can be imported.
   *
   * @return \Drupal\currency\Entity\CurrencyLocaleInterface[]
   */
  public function getImportableCurrencyLocales();

  /**
   * Imports a currency locale.
   *
   * @param string $locale
   *
   * @return \Drupal\currency\Entity\CurrencyLocaleInterface|false
   *   The imported currency locale or FALSE in case of errors.
   */
  public function importCurrencyLocale($locale);

}
