<?php

namespace Drupal\currency\Plugin\Currency\ExchangeRateProvider;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\currency\Annotation\CurrencyExchangeRateProvider;
use Drupal\plugin\Plugin\PluginOperationsProviderPluginManagerTrait;

/**
 * Manages currency exchange rate provider plugins.
 *
 * @see \Drupal\block\BlockInterface
 */
class ExchangeRateProviderManager extends DefaultPluginManager implements ExchangeRateProviderManagerInterface {

  use PluginOperationsProviderPluginManagerTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaults = array(
    'description' => NULL,
  );

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
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ClassResolverInterface $class_resolver) {
    parent::__construct('Plugin/Currency/ExchangeRateProvider', $namespaces, $module_handler, ExchangeRateProviderInterface::class, CurrencyExchangeRateProvider::class);
    $this->alterInfo('currency_exchange_rate_provider');
    $this->setCacheBackend($cache_backend, 'currency_exchange_rate_provider');
    $this->classResolver = $class_resolver;
  }

}
