<?php

namespace Drupal\currency\Entity;

use Commercie\Currency\CurrencyInterface as GenericCurrencyInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines a currency.
 */
interface CurrencyInterface extends GenericCurrencyInterface, ConfigEntityInterface {

  /**
   * Format an amount using this currency and the environment's default currency locale.
   * pattern.
   *
   * @param string $amount
   *   A numeric string.
   * @param boolean $use_currency_precision
   *   Whether or not to use the precision (number of decimals) that the
   *   currency is configured to. If FALSE, the amount will be formatted as-is.
   * @param string $language_type
   *   One of the \Drupal\Core\Language\LanguageInterface\TYPE_* constants.
   *
   * @return string
   */
  function formatAmount($amount, $use_currency_precision = TRUE, $language_type = LanguageInterface::TYPE_CONTENT);

}
