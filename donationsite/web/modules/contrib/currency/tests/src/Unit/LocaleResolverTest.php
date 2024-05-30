<?php

namespace Drupal\Tests\currency\Unit;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Drupal\currency\EventDispatcherInterface;
use Drupal\currency\LocaleResolver;
use Drupal\currency\LocaleResolverInterface;
use Drupal\Tests\UnitTestCase;
use RuntimeException;

/**
 * @coversDefaultClass \Drupal\currency\LocaleResolver
 *
 * @group Currency
 */
class LocaleResolverTest extends UnitTestCase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The currency currency locale storage used for testing
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyLocaleStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The event dispatcher.
   *
   * @var \Drupal\currency\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $eventDispatcher;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\LocaleResolver
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);

    $this->currencyLocaleStorage = $this->createMock(EntityStorageInterface::class);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('currency_locale')
      ->willReturn($this->currencyLocaleStorage);

    $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

    $this->languageManager = $this->createMock(LanguageManagerInterface::class);

    $this->sut = new LocaleResolver($this->entityTypeManager, $this->languageManager, $this->configFactory, $this->eventDispatcher);
  }

  /**
   * @covers ::resolveCurrencyLocale
   */
  function testResolveCurrencyLocaleWithRequestCountry() {
    $this->prepareLanguageManager();

    $request_country_code = 'IN';
    $this->eventDispatcher->expects($this->atLeastOnce())
      ->method('resolveCountryCode')
      ->willReturn($request_country_code);

    $currency_locale = $this->createMock(CurrencyLocaleInterface::class);

    $this->currencyLocaleStorage->expects($this->any())
      ->method('load')
      ->with($this->languageManager->getCurrentLanguage(Language::TYPE_CONTENT)->getId() . '_' . $request_country_code)
      ->willReturn($currency_locale);

    // Test loading the fallback locale.
    $this->assertSame($currency_locale, $this->sut->resolveCurrencyLocale());
  }

  /**
   * @covers ::resolveCurrencyLocale
   */
  function testResolveCurrencyLocaleWithSiteDefaultCountry() {
    $this->prepareLanguageManager();

    $site_default_country = 'IN';

    $config = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->any())
      ->method('get')
      ->with('country.default')
      ->willReturn($site_default_country);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.date')
      ->willReturn($config);

    $currency_locale = $this->createMock(CurrencyLocaleInterface::class);

    $this->currencyLocaleStorage->expects($this->any())
      ->method('load')
      ->with($this->languageManager->getCurrentLanguage(Language::TYPE_CONTENT)->getId() . '_' . $site_default_country)
      ->willReturn($currency_locale);

    // Test loading the fallback locale.
    $this->assertSame($currency_locale, $this->sut->resolveCurrencyLocale());
  }

  /**
   * @covers ::resolveCurrencyLocale
   */
  function testResolveCurrencyLocaleFallback() {
    $this->prepareLanguageManager();

    $config = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->any())
      ->method('get')
      ->with('country.default')
      ->willReturn(NULL);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.date')
      ->willReturn($config);

    $currency_locale = $this->createMock(CurrencyLocaleInterface::class);

    $this->currencyLocaleStorage->expects($this->any())
      ->method('load')
      ->with(LocaleResolverInterface::DEFAULT_LOCALE)
      ->willReturn($currency_locale);

    // Test loading the fallback locale.
    $this->assertSame($currency_locale, $this->sut->resolveCurrencyLocale());
  }

  /**
   * @covers ::resolveCurrencyLocale
   */
  function testResolveCurrencyLocaleMissingFallback() {
    $this->expectException(RuntimeException::class);
    $this->prepareLanguageManager();

    $config = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->any())
      ->method('get')
      ->with('country.default')
      ->willReturn(NULL);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.date')
      ->willReturn($config);

    $this->currencyLocaleStorage->expects($this->any())
      ->method('load')
      ->with(LocaleResolverInterface::DEFAULT_LOCALE)
      ->willReturn(NULL);

    // Test loading the fallback locale.
    $this->sut->resolveCurrencyLocale();
  }

  /**
   * Prepares the language manager for testing.
   */
  protected function prepareLanguageManager() {
    $language_code = $this->randomMachineName(2);
    $language = $this->createMock(LanguageInterface::class);
    $language->expects($this->atLeastOnce())
      ->method('getId')
      ->willReturn($language_code);

    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->with(Language::TYPE_CONTENT)
      ->willReturn($language);
  }

}
