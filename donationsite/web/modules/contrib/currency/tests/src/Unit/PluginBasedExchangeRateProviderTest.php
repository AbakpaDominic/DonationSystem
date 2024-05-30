<?php

namespace Drupal\Tests\currency\Unit;

use Commercie\CurrencyExchange\ExchangeRate;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface;
use Drupal\currency\PluginBasedExchangeRateProvider;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\PluginBasedExchangeRateProvider
 *
 * @group Currency
 */
class PluginBasedExchangeRateProviderTest extends UnitTestCase {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The currency exchanger plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyExchangeRateProviderManager;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\PluginBasedExchangeRateProvider
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->configFactory = $this->getMockBuilder(ConfigFactoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->currencyExchangeRateProviderManager = $this->createMock(ExchangeRateProviderManagerInterface::class);

    $this->sut = new PluginBasedExchangeRateProvider($this->currencyExchangeRateProviderManager, $this->configFactory);
  }

  /**
   * @covers ::loadConfiguration
   */
  public function testLoadConfiguration() {
    $plugin_id_a = $this->randomMachineName();
    $plugin_id_b = $this->randomMachineName();

    $plugin_definitions = array(
      $plugin_id_a => array(),
      $plugin_id_b => array(),
    );

    $config_value = array(
      array(
        'plugin_id' => $plugin_id_b,
        'status' => TRUE,
      ),
    );

    $this->currencyExchangeRateProviderManager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn($plugin_definitions);

    $config = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->once())
      ->method('get')
      ->with('plugins')
      ->willReturn($config_value);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('currency.exchange_rate_provider')
      ->willReturn($config);

    $configuration = $this->sut->loadConfiguration();
    $expected = array(
      $plugin_id_b => TRUE,
      $plugin_id_a => FALSE,
    );
    $this->assertSame($expected, $configuration);
  }

  /**
   * @covers ::saveConfiguration
   */
  public function testSaveConfiguration() {
    $configuration = array(
      'currency_historical_rates' => TRUE,
      'currency_fixed_rates' => TRUE,
      'foo' => FALSE,
    );
    $configuration_data = array(
      array(
        'plugin_id' => 'currency_historical_rates',
        'status' => TRUE,
      ),
      array(
        'plugin_id' => 'currency_fixed_rates',
        'status' => TRUE,
      ),
      array(
        'plugin_id' => 'foo',
        'status' => FALSE,
      ),
    );

    $config = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->once())
      ->method('set')
      ->with('plugins', $configuration_data);
    $config->expects($this->once())
      ->method('save');

    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('currency.exchange_rate_provider')
      ->willReturn($config);

    $this->sut->saveConfiguration($configuration);
  }

  /**
   * @covers ::load
   */
  public function testLoad() {
    $currency_code_from = 'EUR';
    $currency_code_to = 'NLG';
    $rate = new ExchangeRate($currency_code_from, $currency_code_to, '2.20371');

    $exchange_rate_provider_id_a = $this->randomMachineName();

    $exchange_rate_provider_id_b = $this->randomMachineName();
    $exchange_rate_provider_b = $this->createMock('\Commercie\CurrencyExchange\ExchangeRateProviderInterface');
    $exchange_rate_provider_b->expects($this->once())
      ->method('load')
      ->with($currency_code_from, $currency_code_to)
      ->willReturn($rate);

    $plugin_definitions = [
      $exchange_rate_provider_id_a => [
        'id' => $exchange_rate_provider_id_a,
      ],
      $exchange_rate_provider_id_b => [
        'id' => $exchange_rate_provider_id_b,
      ],
    ];
    $this->currencyExchangeRateProviderManager->expects($this->once())
      ->method('createInstance')
      ->with($exchange_rate_provider_id_b)
      ->willReturn($exchange_rate_provider_b);
    $this->currencyExchangeRateProviderManager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn($plugin_definitions);

    $config_value = [
      [
        'plugin_id' => $exchange_rate_provider_id_a,
        'status' => FALSE,
      ],
      [
        'plugin_id' => $exchange_rate_provider_id_b,
        'status' => TRUE,
      ],
    ];
    $config = $this->getMockBuilder('\Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();

    $config->expects($this->once())
      ->method('get')
      ->with('plugins')
      ->will($this->returnValue($config_value));

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('currency.exchange_rate_provider')
      ->will($this->returnValue($config));

    $this->assertSame($rate, $this->sut->load($currency_code_from, $currency_code_to));
  }

}
