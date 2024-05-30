<?php

namespace Drupal\currency;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Gets the right locale for the environment.
 */
class LocaleResolver implements LocaleResolverInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The currency currency locale storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyLocaleStorage;

  /**
   * The event dispatcher.
   *
   * @var \Drupal\currency\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The currency locales to use.
   *
   * @var \Drupal\currency\Entity\CurrencyLocaleInterface[]
   *   Keys are \Drupal\Core\Language\LanguageInterface::TYPE_* constants.
   */
  protected $currencyLocales = [];

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\currency\EventDispatcherInterface $event_dispatcher
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory, EventDispatcherInterface $event_dispatcher) {
    $this->configFactory = $config_factory;
    $this->currencyLocaleStorage = $entity_type_manager->getStorage('currency_locale');
    $this->eventDispatcher = $event_dispatcher;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveCurrencyLocale($language_type = LanguageInterface::TYPE_CONTENT) {
    if (empty($this->currencyLocales[$language_type])) {
      $currency_locale = NULL;
      $language_code = $this->languageManager->getCurrentLanguage($language_type)->getId();

      // Try this request's country code.
      $country_code = $this->eventDispatcher->resolveCountryCode();
      if ($country_code) {
        $currency_locale = $this->currencyLocaleStorage->load($language_code . '_' . $country_code);
      }

      // Try the site's default country code.
      if (!$currency_locale) {
        $country_code = $this->configFactory->get('system.date')->get('country.default');
        if ($country_code) {
          $currency_locale = $this->currencyLocaleStorage->load($language_code . '_' . $country_code);
        }

      }

      // Try the Currency default.
      if (!$currency_locale) {
        $currency_locale = $this->currencyLocaleStorage->load($this::DEFAULT_LOCALE);
      }

      if ($currency_locale) {
        $this->currencyLocales[$language_type] = $currency_locale;
      }
      else {
        throw new \RuntimeException(sprintf('The currency locale for %s could not be loaded.', $this::DEFAULT_LOCALE));
      }
    }

    return $this->currencyLocales[$language_type];
  }

}
