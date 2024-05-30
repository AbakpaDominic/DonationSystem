<?php

namespace Drupal\currency;

use Commercie\Currency\ResourceRepository;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a config importer.
 */
class ConfigImporter implements ConfigImporterInterface {

  /**
   * The currency resource repository.
   *
   * @var \Commercie\Currency\ResourceRepository
   */
  protected $currencyResourceRepository;

  /**
   * The config storage.
   *
   * @see self::getConfigStorage()
   * @see self::setConfigStorage()
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * The currency entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * The currency locale entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyLocaleStorage;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *   THe typed config manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ModuleHandlerInterface $module_handler, TypedConfigManagerInterface $typed_config_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->currencyResourceRepository = new ResourceRepository();
    $this->currencyStorage = $entity_type_manager->getStorage('currency');
    $this->currencyLocaleStorage = $entity_type_manager->getStorage('currency_locale');
    $this->moduleHandler = $module_handler;
    $this->typedConfigManager = $typed_config_manager;
  }

  /**
   * Gets the currency config storage.
   *
   * @return \Drupal\Core\Config\StorageInterface
   */
  protected function getConfigStorage() {
    if (!$this->configStorage) {
      $this->configStorage = new FileStorage($this->moduleHandler->getModule('currency')->getPath() . '/config/default/');
    }

    return $this->configStorage;
  }

  /**
   * Sets the config storage.
   *
   * @param \Drupal\Core\Config\StorageInterface
   */
  public function setConfigStorage(StorageInterface $storage) {
    $this->configStorage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getImportableCurrencies() {
    $existing_currencies = $this->currencyStorage->loadMultiple();
    $currencies = [];
    foreach ($this->currencyResourceRepository->listCurrencies() as $currency_code) {
      if (!isset($existing_currencies[$currency_code])) {
        $currencies[$currency_code] = $this->createCurrencyFromRepository($currency_code);
      }
    }

    return $currencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getImportableCurrencyLocales() {
    $existing_currency_locales = $this->currencyLocaleStorage->loadMultiple();
    $currency_locales = [];
    $prefix = 'currency.currency_locale.';
    foreach ($this->getConfigStorage()->listAll($prefix) as $name) {
      if (!isset($existing_currency_locales[substr($name, strlen($prefix))])) {
        $currency_locales[] = $this->currencyLocaleStorage->create($this->getConfigStorage()->read($name));
      }
    }

    return $currency_locales;
  }

  /**
   * {@inheritdoc}
   */
  public function importCurrency($currency_code) {
    if (!$this->currencyStorage->load($currency_code)) {
      $currency = $this->createCurrencyFromRepository($currency_code);
      $currency->save();
      return $currency;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function importCurrencyLocale($locale) {
    if (!$this->currencyLocaleStorage->load($locale)) {
      $name = 'currency.currency_locale.' . $locale;
      $currency_locale = $this->currencyLocaleStorage->create($this->getConfigStorage()->read($name));
      $currency_locale->save();
      return $currency_locale;
    }
    return FALSE;
  }

  /**
   * Creates a currency entity from a currency from the repository.
   *
   * @param string $currency_code
   *
   * @return \Drupal\currency\Entity\CurrencyInterface
   *
   * @throws \InvalidArgumentException
   *   Thrown when no currency with the given currency code exists.
   */
  protected function createCurrencyFromRepository($currency_code) {
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency_entity */
    $currency_entity = $this->currencyStorage->create();
    $currency_resource = $this->currencyResourceRepository->loadCurrency($currency_code);
    if (is_null($currency_resource)) {
      throw new \InvalidArgumentException(sprintf('No currency with currency code %s exists.', $currency_code));
    }
    $currency_entity->setCurrencyCode($currency_resource->getCurrencyCode());
    $currency_entity->setCurrencyNumber($currency_resource->getCurrencyNumber());
    $currency_entity->setLabel($currency_resource->getLabel());
    $currency_entity->setSign($currency_resource->getSign());
    $currency_entity->setAlternativeSigns($currency_resource->getAlternativeSigns());
    $currency_entity->setSubunits($currency_resource->getSubunits());
    $currency_entity->setRoundingStep($currency_resource->getRoundingStep());
    $currency_entity->setUsages($currency_resource->getUsages());

    return $currency_entity;
  }

}
