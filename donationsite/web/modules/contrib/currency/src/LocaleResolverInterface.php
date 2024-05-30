<?php

namespace Drupal\currency;

use Drupal\Core\Language\LanguageInterface;

/**
 * Defines a locale resolver.
 */
interface LocaleResolverInterface {

  /**
   * The default locale.
   */
  const DEFAULT_LOCALE = 'en_US';

  /**
   * Loads the locale to use.
   *
   * @param string $language_type
   *   One of the \Drupal\Core\Language\LanguageInterface\TYPE_* constants.
   *
   * @throws \RuntimeException
   *
   * @return \Drupal\currency\Entity\CurrencyLocaleInterface
   */
  public function resolveCurrencyLocale($language_type = LanguageInterface::TYPE_CONTENT);

}
