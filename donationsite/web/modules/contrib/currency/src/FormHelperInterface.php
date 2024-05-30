<?php

namespace Drupal\currency;

/**
 * Defines form helpers.
 */
interface FormHelperInterface {

  /**
   * Returns an options list of all currencies.
   *
   * @param \Drupal\currency\Entity\CurrencyInterface[]|null $currencies
   *   An array of currencies to limit the options by, or NULL to allow all
   *   currencies to be selected.
   *
   * @return array
   *   Keys are currency codes. Values are human-readable currency labels.
   */
  public function getCurrencyOptions(array $currencies = NULL);

  /**
   * Returns an options list of all currency locales.
   *
   * @param \Drupal\currency\Entity\CurrencyLocaleInterface[]|null $currency_locales
   *   An array of currency locales to limit the options by, or NULL to allow
   *   all currency locales to be selected.
   *
   * @return array
   *   Keys are locales. Values are human-readable currency locale labels.
   */
  public function getCurrencyLocaleOptions(array $currency_locales = NULL);

}
