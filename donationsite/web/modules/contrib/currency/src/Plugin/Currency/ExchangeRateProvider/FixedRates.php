<?php

namespace Drupal\currency\Plugin\Currency\ExchangeRateProvider;

use Commercie\CurrencyExchange\FixedExchangeRateProviderTrait;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides manually entered exchange rates.
 *
 * @CurrencyExchangeRateProvider(
 *   id = "currency_fixed_rates",
 *   label = @Translation("Fixed rates"),
 *   operations_provider = "\Drupal\currency\Plugin\Currency\ExchangeRateProvider\FixedRatesOperationsProvider"
 * )
 */
class FixedRates extends PluginBase implements ExchangeRateProviderInterface, ContainerFactoryPluginInterface {

  use FixedExchangeRateProviderTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new class instance
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed[] $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function loadAll() {
    $rates_data = $this->configFactory->get('currency.exchanger.fixed_rates')->get('rates');
    $rates = array();
    foreach ($rates_data as $rate_data) {
      $rates[$rate_data['currency_code_from']][$rate_data['currency_code_to']] = $rate_data['rate'];
    }

    return $rates;
  }

  /**
   * Saves an exchange rate.
   *
   * @param string $currency_code_from
   * @param string $currency_code_to
   * @param string $rate
   *
   * @return $this
   */
  public function save($currency_code_from, $currency_code_to, $rate) {
    $config = $this->configFactory->getEditable('currency.exchanger.fixed_rates');
    $rates = $this->loadAll();
    $rates[$currency_code_from][$currency_code_to] = $rate;
    // Massage the rates into a format that can be stored, as associative
    // arrays are not supported by the config system
    $rates_data = array();
    foreach ($rates as $currency_code_from => $currency_code_from_rates) {
      foreach ($currency_code_from_rates as $currency_code_to => $rate) {
        $rates_data[] = array(
          'currency_code_from' => $currency_code_from,
          'currency_code_to' => $currency_code_to,
          'rate' => $rate,
        );
      }
    }

    $config->set('rates', $rates_data);
    $config->save();

    return $this;
  }

  /**
   * Deletes an exchange rate.
   *
   * @param string $currency_code_from
   * @param string $currency_code_to
   *
   * @return NULL
   */
  public function delete($currency_code_from, $currency_code_to) {
    $config = $this->configFactory->getEditable('currency.exchanger.fixed_rates');
    $rates = $config->get('rates');
    foreach ($rates as $i => $rate) {
      if ($rate['currency_code_from'] == $currency_code_from && $rate['currency_code_to'] == $currency_code_to) {
        unset($rates[$i]);
      }
    }
    $config->set('rates', $rates);
    $config->save();
  }
}
