<?php

namespace Drupal\Tests\currency\Unit\Controller;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\currency\Controller\FixedRatesOverview;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\FixedRates;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Controller\FixedRatesOverview
 *
 * @group Currency
 */
class FixedRatesOverviewTest extends UnitTestCase {

  /**
   * The currency amount formatter manager.
   *
   * @var \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyAmountFormatterManager;

  /**
   * The currency exchange rate provider manager.
   *
   * @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyExchangeRateProviderManager;

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyStorage;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Controller\FixedRatesOverview
   */
  protected $sut;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->currencyStorage = $this->createMock(EntityStorageInterface::class);

    $this->currencyAmountFormatterManager = $this->createMock(AmountFormatterManagerInterface::class);

    $this->currencyExchangeRateProviderManager = $this->createMock(ExchangeRateProviderManagerInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

    $this->sut = new FixedRatesOverview($this->stringTranslation, $this->urlGenerator, $this->currencyStorage, $this->currencyAmountFormatterManager, $this->currencyExchangeRateProviderManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->expects($this->atLeastOnce())
      ->method('getStorage')
      ->with('currency')
      ->willReturn($this->currencyStorage);

    $container = $this->createMock(ContainerInterface::class);
    $map = array(
      array('entity_type.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_type_manager),
      array('plugin.manager.currency.amount_formatter', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currencyAmountFormatterManager),
      array('plugin.manager.currency.exchange_rate_provider', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currencyExchangeRateProviderManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      array('url_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->urlGenerator),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = FixedRatesOverview::create($container);
    $this->assertInstanceOf(FixedRatesOverview::class, $sut);
  }

  /**
   * @covers ::overview
   */
  public function testOverview() {
    $currency_code_from = 'EUR';
    $currency_code_to = 'NLG';
    $rate = '2.20371';

    $currency_from = $this->createMock(CurrencyInterface::class);
    $currency_from->expects($this->once())
      ->method('label');

    $currency_to = $this->createMock(CurrencyInterface::class);
    $currency_to->expects($this->once())
      ->method('label');

    $map = array(
      array($currency_code_from, $currency_from),
      array($currency_code_to, $currency_to),
    );
    $this->currencyStorage->expects($this->any())
      ->method('load')
      ->willReturnMap($map);

    $rates_configuration = array(
      $currency_code_from => array(
        $currency_code_to => $rate,
      ),
    );
    $fixed_rates = $this->getMockBuilder(FixedRates::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fixed_rates->expects($this->once())
      ->method('loadAll')
      ->willReturn($rates_configuration);

    $this->currencyExchangeRateProviderManager->expects($this->once())
      ->method('createInstance')
      ->with('currency_fixed_rates')
      ->willReturn($fixed_rates);

    $this->urlGenerator->expects($this->once())
      ->method('generateFromRoute')
      ->with('currency.exchange_rate_provider.fixed_rates.add');

    $amount_formatter = $this->createMock(AmountFormatterInterface::class);
    $amount_formatter->expects($this->once())
      ->method('formatAmount')
      ->with($currency_to, $rate);

    $this->currencyAmountFormatterManager->expects($this->once())
      ->method('getDefaultPlugin')
      ->willReturn($amount_formatter);

    $build = $this->sut->overview();
    $this->assertIsArray($build);
  }
}
