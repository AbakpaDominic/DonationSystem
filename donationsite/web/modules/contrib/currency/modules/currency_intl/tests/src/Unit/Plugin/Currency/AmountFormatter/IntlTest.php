<?php

namespace Drupal\Tests\currency_intl\Unit\Plugin\Currency\AmountFormatter;

use Commercie\Currency\CurrencyInterface;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Drupal\currency\LocaleResolverInterface;
use Drupal\currency_intl\Plugin\Currency\AmountFormatter\Intl;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency_intl\Plugin\Currency\AmountFormatter\Intl
 *
 * @group Currency Intl
 * @requires extension intl
 */
class IntlTest extends UnitTestCase {

  /**
   * The formatter under test.
   *
   * @var \Drupal\currency_intl\Plugin\Currency\AmountFormatter\Intl
   */
  protected $formatter;

  /**
   * The locale resolver.
   *
   * @var \Drupal\currency\LocaleResolverInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $localeResolver;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();

    $this->localeResolver = $this->createMock(LocaleResolverInterface::class);

    $this->formatter = new Intl($configuration, $plugin_id, $plugin_definition, $this->localeResolver);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->createMock(ContainerInterface::class);
    $container->expects($this->once())
      ->method('get')
      ->with('currency.locale_resolver')
      ->willReturn($this->localeResolver);

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();

    $formatter = Intl::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(Intl::class, $formatter);
  }

  /**
   * @covers ::formatAmount
   */
  function testFormatAmount() {
    $locale = 'nl_NL';
    $pattern = '¤-#,##0.00¤¤';
    $decimal_separator = '@';
    $grouping_separator ='%';
    $currency_locale = $this->createMock(CurrencyLocaleInterface::class);
    $currency_locale->expects($this->any())
      ->method('getLocale')
      ->willReturn($locale);
    $currency_locale->expects($this->any())
      ->method('getPattern')
      ->willReturn($pattern);
    $currency_locale->expects($this->any())
      ->method('getDecimalSeparator')
      ->willReturn($decimal_separator);
    $currency_locale->expects($this->any())
      ->method('getGroupingSeparator')
      ->willReturn($grouping_separator);

    $this->localeResolver->expects($this->any())
      ->method('resolveCurrencyLocale')
      ->willReturn($currency_locale);

    // ICU, the C library that PHP's Intl extension uses for formatting, is
    // known to have trouble formatting combinations of currencies and locales
    // that it does not know. In order to make sure this works, test such a
    // combination, such as the Ukrainian Hryvnia (UAH) with Dutch, Netherlands
    // (nl_NL).
    $currency_sign = '₴';
    $currency_code = 'UAH';
    $currency = $this->createMock(CurrencyInterface::class);
    $currency->expects($this->any())
      ->method('getCurrencyCode')
      ->willReturn($currency_code);
    $currency->expects($this->any())
      ->method('getSign')
      ->willReturn($currency_sign);

    $results = array(
      // An amount with no decimals should be formatted without decimals and
      // decimal separator.
      '123' => '₴-123 UAH',
      // An amount with three groupings should have two grouping separators. All
      // of its three decimals should be formatted, even if the currency only
      // has two.
      '1234567.890' => '₴-1%234%567@890 UAH',
      // An amount with only one decimal should be formatted with only one.
      '.3' => '₴-0@3 UAH',
    );
    foreach ($results as $amount=> $expected) {
      $formatted = $this->formatter->formatAmount($currency, $amount);
      $this->assertSame($expected, $formatted);
    }
  }
}
