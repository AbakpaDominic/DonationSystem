<?php

namespace Drupal\currency\Plugin\Currency\AmountFormatter;

use Commercie\Currency\CurrencyInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines a plugin type to format amounts.
 */
interface AmountFormatterInterface extends PluginInspectionInterface {

  /**
   * Formats an amount.
   *
   * @param \Commercie\Currency\CurrencyInterface $currency
   *   The currency the amount is in.
   * @param string $amount
   *   A numeric string.
   * @param string $language_type
   *   One of the \Drupal\Core\Language\LanguageInterface\TYPE_* constants.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   */
  function formatAmount(CurrencyInterface $currency, $amount, $language_type = LanguageInterface::TYPE_CONTENT);
}
