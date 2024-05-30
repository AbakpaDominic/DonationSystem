<?php

namespace Drupal\Tests\currency\Unit\Plugin\Currency\AmountFormatter;

use Commercie\Currency\CurrencyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Drupal\currency\LocaleResolverInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\Basic;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\Currency\AmountFormatter\Basic
 *
 * @group Currency
 */
class BasicTest extends UnitTestCase {

  /**
   * The locale resolver.
   *
   * @var \Drupal\currency\LocaleResolverInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $localeResolver;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Plugin\Currency\AmountFormatter\Basic
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();

    $this->localeResolver = $this->createMock(LocaleResolverInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new Basic($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->localeResolver);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->createMock(ContainerInterface::class);
    $map = array(
      array('currency.locale_resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->localeResolver),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();

    $sut = Basic::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(Basic::class, $sut);
  }

  /**
   * @covers ::formatAmount
   */
  function testFormatAmount() {
    $decimal_separator = '@';
    $grouping_separator ='%';
    $currency_locale = $this->createMock(CurrencyLocaleInterface::class);
    $currency_locale->expects($this->any())
      ->method('getDecimalSeparator')
      ->willReturn($decimal_separator);
    $currency_locale->expects($this->any())
      ->method('getGroupingSeparator')
      ->willReturn($grouping_separator);

    $this->localeResolver->expects($this->any())
      ->method('resolveCurrencyLocale')
      ->willReturn($currency_locale);

    // The formatter must not alter the decimals.
    $amount = '987654.321';

    $currency_sign = '₴';
    $currency_code = 'UAH';
    $currency_decimals = 2;
    $currency = $this->createMock(CurrencyInterface::class);
    $currency->expects($this->any())
      ->method('getCurrencyCode')
      ->willReturn($currency_code);
    $currency->expects($this->any())
      ->method('getDecimals')
      ->willReturn($currency_decimals);
    $currency->expects($this->any())
      ->method('getSign')
      ->willReturn($currency_sign);

    $translation = 'UAH 987%654@321';

    $arguments = array (
      '@currency_code' => 'UAH',
      '@currency_sign' => '₴',
      '@amount' => '987%654@321',
    );

    $formatted_amount = $this->sut->formatAmount($currency, $amount);
    $this->assertInstanceOf(TranslatableMarkup::class, $formatted_amount);
    $this->assertSame('@currency_code @amount', $formatted_amount->getUntranslatedString());
    $this->assertSame($arguments, $formatted_amount->getArguments());
  }
}
