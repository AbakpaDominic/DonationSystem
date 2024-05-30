<?php

namespace Drupal\currency;

use Commercie\CurrencyExchange\AbstractStackedExchangeRateProvider;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides currency exchange rates through plugins.
 */
class PluginBasedExchangeRateProvider extends AbstractStackedExchangeRateProvider {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The currency exchanger plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $currencyExchangeRateProviderManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $currency_exchange_rate_provider_manager
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  function __construct(PluginManagerInterface $currency_exchange_rate_provider_manager, ConfigFactoryInterface $config_factory) {
    $this->currencyExchangeRateProviderManager = $currency_exchange_rate_provider_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Loads the plugin configuration.
   *
   * @return bool[]
   *   Keys are currency_exchanger plugin names. Values are booleans that
   *   describe whether the plugins are enabled. Items are ordered by weight.
   */
  public function loadConfiguration() {
    $definitions = $this->currencyExchangeRateProviderManager->getDefinitions();
    $configuration_data = $this->configFactory->get('currency.exchange_rate_provider')->get('plugins');
    $configuration = array();
    foreach ($configuration_data as $data) {
      $configuration[$data['plugin_id']] = $data['status'];
    }

    return $configuration + array_fill_keys(array_keys($definitions), FALSE);
  }

  /**
   * Saves the configuration.
   *
   * @param bool[] $configuration
   *   Keys are currency_exchanger plugin names. Values are booleans that
   *   describe whether the plugins are enabled. Items are ordered by weight.
   *
   * @return $this
   */
  public function saveConfiguration(array $configuration) {
    $config = $this->configFactory->getEditable('currency.exchange_rate_provider');
    // Massage the configuration into a format that can be stored, as
    // associative arrays are not supported by the config system
    $configuration_data = array();
    foreach ($configuration as $plugin_id => $status) {
      $configuration_data[] = array(
        'plugin_id' => $plugin_id,
        'status' => $status,
      );
    }
    $config->set('plugins', $configuration_data);
    $config->save();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExchangeRateProviders() {
    $plugin_ids = array_keys(array_filter($this->loadConfiguration()));
    $plugins = array();
    foreach ($plugin_ids as $plugin_id) {
      $plugins[$plugin_id] = $this->currencyExchangeRateProviderManager->createInstance($plugin_id);
    }

    return $plugins;
  }

}
