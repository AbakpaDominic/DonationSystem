<?php

namespace Drupal\Tests\currency\Unit;

use Commercie\CurrencyExchange\ExchangeRate as GenericExchangeRate;
use Drupal\currency\ExchangeRate;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\ExchangeRate
 *
 * @group Currency
 */
class ExchangeRateTest extends UnitTestCase {

  /**
   * The subject under test.
   *
   * @var \Drupal\currency\ExchangeRate
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $exchange_rate_provider_id = $this->randomMachineName();
    $timestamp = mt_rand();
    $source_currency_code = $this->randomMachineName(3);
    $destination_currency_code = $this->randomMachineName(3);
    $rate = mt_rand();
    $this->sut = new ExchangeRate($exchange_rate_provider_id, $timestamp, $source_currency_code, $destination_currency_code, $rate);
  }

  /**
   * @covers ::createFromExchangeRate
   */
  public function testCreateFromExchangeRate() {
    $exchange_rate_provider_id = $this->randomMachineName();
    $timestamp = mt_rand();
    $source_currency_code = $this->randomMachineName(3);
    $destination_currency_code = $this->randomMachineName(3);
    $rate = mt_rand();
    $other_exchange_rate = new GenericExchangeRate($source_currency_code, $destination_currency_code, $rate);
    $other_exchange_rate->setTimestamp($timestamp);

    $created_exchange_rate = ExchangeRate::createFromExchangeRate($other_exchange_rate, $exchange_rate_provider_id);

    $this->assertSame($other_exchange_rate->getSourceCurrencyCode(), $created_exchange_rate->getSourceCurrencyCode());
    $this->assertSame($other_exchange_rate->getDestinationCurrencyCode(), $created_exchange_rate->getDestinationCurrencyCode());
    $this->assertSame($other_exchange_rate->getRate(), $created_exchange_rate->getRate());
    $this->assertSame($other_exchange_rate->getTimestamp(), $created_exchange_rate->getTimestamp());
    $this->assertSame($exchange_rate_provider_id, $created_exchange_rate->getExchangeRateProviderId());
  }

  /**
   * @covers ::getExchangeRateProviderId
   * @covers ::setExchangeRateProviderId
   */
  public function testGetExchangeRateProviderId() {
    $id = $this->randomMachineName();
    $this->assertSame($this->sut, $this->sut->setExchangeRateProviderId($id));
    $this->assertSame($id, $this->sut->getExchangeRateProviderId());
  }

}
