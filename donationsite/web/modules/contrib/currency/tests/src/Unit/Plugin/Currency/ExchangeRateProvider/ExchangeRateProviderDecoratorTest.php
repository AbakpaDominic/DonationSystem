<?php

namespace Drupal\Tests\currency\Unit\Plugin\Currency\ExchangeRateProvider;

use Commercie\CurrencyExchange\ExchangeRateProviderInterface;
use Drupal\currency\ExchangeRate;
use Drupal\currency\ExchangeRateInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderDecorator;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderDecorator
 *
 * @group Currency
 */
class ExchangeRateProviderDecoratorTest extends UnitTestCase {

  /**
   * The decorated exchange rate provider
   *
   * @var \Commercie\CurrencyExchange\ExchangeRateProviderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $exchangeRateProvider;

  /**
   * The plugin ID.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\HistoricalRates
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->exchangeRateProvider = $this->createMock(ExchangeRateProviderInterface::class);

    $configuration = array();
    $this->pluginId = $this->randomMachineName();
    $plugin_definition = array();

    $this->sut = new ExchangeRateProviderDecorator($configuration, $this->pluginId, $plugin_definition, $this->exchangeRateProvider);
  }

  /**
   * @covers ::load
   */
  public function testLoad() {
    $source_currency_code = $this->randomMachineName();
    $destination_currency_code = $this->randomMachineName();
    $rate = mt_rand();
    $exchange_rate = new ExchangeRate($source_currency_code, $destination_currency_code, $rate, $this->pluginId);

    $this->exchangeRateProvider->expects($this->once())
      ->method('load')
      ->with($source_currency_code, $destination_currency_code)
      ->willReturn($exchange_rate);

    $this->assertExchangeRateEquals($exchange_rate, $this->sut->load($source_currency_code, $destination_currency_code));
  }

  /**
   * @covers ::loadMultiple
   */
  public function testLoadMultiple() {
    $source_currency_code = $this->randomMachineName();
    $destination_currency_code_a = $this->randomMachineName();
    $destination_currency_code_b = $this->randomMachineName();
    $exchange_rate_a = new ExchangeRate($source_currency_code, $destination_currency_code_a, mt_rand(), $this->pluginId);
    $exchange_rate_b = new ExchangeRate($source_currency_code, $destination_currency_code_b, mt_rand(), $this->pluginId);
    $exchange_rates = [
      $source_currency_code => [
        $destination_currency_code_a => $exchange_rate_a,
        $destination_currency_code_b => $exchange_rate_b,
      ],
    ];

    $this->exchangeRateProvider->expects($this->once())
      ->method('loadMultiple')
      ->with([
        $source_currency_code => [$destination_currency_code_a, $destination_currency_code_b],
      ])
      ->willReturn($exchange_rates);

    $loaded_exchange_rates = $this->sut->loadMultiple([
      $source_currency_code => [$destination_currency_code_a, $destination_currency_code_b],
    ]);

    $this->assertExchangeRateEquals($exchange_rate_a, $loaded_exchange_rates[$source_currency_code][$destination_currency_code_a]);
    $this->assertExchangeRateEquals($exchange_rate_b, $loaded_exchange_rates[$source_currency_code][$destination_currency_code_b]);
  }

  /**
   * Asserts that two exchange rates are equal.
   *
   * @param \Drupal\currency\ExchangeRateInterface $expected
   * @param \Drupal\currency\ExchangeRateInterface $real
   */
  protected function assertExchangeRateEquals(ExchangeRateInterface $expected, ExchangeRateInterface $real) {
    $this->assertSame($expected->getSourceCurrencyCode(), $real->getSourceCurrencyCode());
    $this->assertSame($expected->getDestinationCurrencyCode(), $real->getDestinationCurrencyCode());
    $this->assertSame($expected->getRate(), $real->getRate());
    $this->assertSame($expected->getTimestamp(), $real->getTimestamp());
    $this->assertSame($expected->getExchangeRateProviderId(), $real->getExchangeRateProviderId());
  }

}
