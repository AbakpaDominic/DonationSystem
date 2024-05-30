<?php

namespace Drupal\currency\Plugin\Currency\ExchangeRateProvider;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\plugin\PluginOperationsProviderProviderInterface;

/**
 * Defines an amount formatter plugin manager.
 */
interface ExchangeRateProviderManagerInterface extends PluginManagerInterface, PluginOperationsProviderProviderInterface {

  /**
   * Creates an exchange rate provider.
   *
   * @param string $plugin_id
   *   The id of the plugin being instantiated.
   * @param mixed[] $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderInterface
   */
  public function createInstance($plugin_id, array $configuration = array());

}
