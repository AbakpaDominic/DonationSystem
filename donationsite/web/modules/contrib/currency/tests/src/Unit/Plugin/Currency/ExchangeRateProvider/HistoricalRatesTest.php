<?php

namespace Drupal\Tests\currency\Unit\Plugin\Currency\ExchangeRateProvider;

use Commercie\CurrencyExchange\ExchangeRateProviderInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\HistoricalRates;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\Currency\ExchangeRateProvider\HistoricalRates
 *
 * @group Currency
 */
class HistoricalRatesTest extends UnitTestCase {

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
    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();

    $this->sut = new HistoricalRates($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * @covers ::load
   */
  public function testLoad() {
    $source_currency_code = 'EUR';
    $destination_currency_code = 'NLG';
    $exchange_rate = '2.20371';

    $this->assertSame($exchange_rate, $this->sut->load($source_currency_code, $destination_currency_code)->getRate());
  }

  /**
   * @covers ::loadMultiple
   */
  public function testLoadMultiple() {
    $source_currency_code = 'EUR';
    $destination_currency_code_a = 'NLG';
    $rate_a = '2.20371';
    $destination_currency_code_b = 'BEF';
    $rate_b = '40.3399';

    $exchange_rates = $this->sut->loadMultiple([
      $source_currency_code => [$destination_currency_code_a, $destination_currency_code_b],
    ]);

    $this->assertSame($rate_a, $exchange_rates[$source_currency_code][$destination_currency_code_a]->getRate());
    $this->assertSame($rate_b, $exchange_rates[$source_currency_code][$destination_currency_code_b]->getRate());
  }

}
