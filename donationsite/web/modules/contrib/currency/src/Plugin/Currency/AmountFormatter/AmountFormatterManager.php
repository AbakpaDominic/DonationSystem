<?php

namespace Drupal\currency\Plugin\Currency\AmountFormatter;

use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\currency\Annotation\CurrencyAmountFormatter;

/**
 * Manages amount formatter plugins.
 */
class AmountFormatterManager extends DefaultPluginManager implements AmountFormatterManagerInterface, FallbackPluginManagerInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new instance.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory) {
    parent::__construct('Plugin/Currency/AmountFormatter', $namespaces, $module_handler, AmountFormatterInterface::class, CurrencyAmountFormatter::class);
    $this->alterInfo('currency_amount_formatter');
    $this->setCacheBackend($cache_backend, 'currency_amount_formatter');
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = array()) {
    return 'currency_basic';
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultPluginId() {
    return $this->configFactory->get('currency.amount_formatting')
      ->get('plugin_id');
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultPluginId($plugin_id) {
    $this->configFactory->get('currency.amount_formatting')
      ->set('plugin_id', $plugin_id)
      ->save();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultPlugin() {
    return $this->createInstance($this->getDefaultPluginId());
  }

}
