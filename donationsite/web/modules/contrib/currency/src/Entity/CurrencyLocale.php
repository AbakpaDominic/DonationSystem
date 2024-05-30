<?php

namespace Drupal\currency\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines a currency locale entity.
 *
 * @ConfigEntityType(
 *   handlers = {
 *     "access" = "Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleForm",
 *       "delete" = "Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleDeleteForm"
 *     },
 *     "list_builder" = "Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleListBuilder",
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *   },
 *   entity_keys = {
 *     "id" = "locale",
 *     "label" = "locale",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "decimalSeparator",
 *     "groupingSeparator",
 *     "locale",
 *     "pattern",
 *     "uuid",
 *   },
 *   id = "currency_locale",
 *   label = @Translation("Currency currency locale"),
 *   links = {
 *     "collection" = "/admin/config/regional/currency-formatting/locale",
 *     "create-form" = "/admin/config/regional/currency-formatting/locale/add",
 *     "edit-form" = "/admin/config/regional/currency-formatting/locale/{currency_locale}",
 *     "delete-form" = "/admin/config/regional/currency-formatting/locale/{currency_locale}/delete"
 *   }
 * )
 */
class CurrencyLocale extends ConfigEntityBase implements CurrencyLocaleInterface {

  use StringTranslationTrait;

  /**
   * The country manager.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * The decimal separator character.
   *
   * @var string
   */
  protected $decimalSeparator = NULL;

  /**
   * The grouping separator character.
   *
   * @var string
   */
  protected $groupingSeparator = NULL;

  /**
   * The locale identifier.
   *
   * The identifier consists of a language code, an underscore, and a country
   * code. Examples: nl_NL, en_US.
   *
   * @var string
   */
  public $locale = NULL;

  /**
   * The Unicode CLDR number pattern.
   *
   * @var string
   */
  protected $pattern = NULL;

  /**
   * The UUID for this entity.
   *
   * @var string
   */
  public $uuid = NULL;

  /**
   * {@inheritdoc}
   */
  public function setDecimalSeparator($separator) {
    $this->decimalSeparator = $separator;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecimalSeparator() {
    return $this->decimalSeparator;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroupingSeparator($separator) {
    $this->groupingSeparator = $separator;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupingSeparator() {
    return $this->groupingSeparator;
  }

  /**
   * {@inheritdoc}
   */
  public function setLocale($language_code, $country_code) {
    $this->locale = strtolower($language_code) . '_' . strtoupper($country_code);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocale() {
    return $this->locale;
  }

  /**
   * {@inheritdoc}
   */
  public function setPattern($pattern) {
    $this->pattern = $pattern;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPattern() {
    return $this->pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->locale;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $languages = LanguageManager::getStandardLanguageList();
    $countries = $this->getCountryManager()->getList();

    return $this->t('@language (@country)', [
      '@language' => isset($languages[$this->getLanguageCode()]) ? $languages[$this->getLanguageCode()][0] : $this->getLanguageCode(),
      '@country' => isset($countries[$this->getCountryCode()]) ? $countries[$this->getCountryCode()] : $this->getCountryCode(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageCode() {
    if ($this->id()) {
      $fragments = explode('_', $this->id());
      return $fragments[0];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCountryCode() {
    if ($this->id()) {
      $fragments = explode('_', $this->id());
      return $fragments[1];
    }
  }

  /**
   * Sets the country manager.
   *
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *
   * @return $this
   */
  public function setCountryManager(CountryManagerInterface $country_manager) {
    $this->countryManager = $country_manager;

    return $this;
  }

  /**
   * Gets the country manager.
   *
   * @return \Drupal\Core\Locale\CountryManagerInterface
   */
  protected function getCountryManager() {
    if (!$this->countryManager) {
      $this->countryManager = \Drupal::service('country_manager');
    }

    return $this->countryManager;
  }

}
