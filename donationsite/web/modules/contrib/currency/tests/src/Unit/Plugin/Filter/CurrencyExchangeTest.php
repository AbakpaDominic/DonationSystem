<?php

namespace Drupal\Tests\currency\Unit\Plugin\Filter;

use Commercie\Currency\InputInterface;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\currency\ExchangeRate;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderInterface;
use Drupal\currency\Plugin\Filter\CurrencyExchange;
use Drupal\filter\FilterProcessResult;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\Filter\CurrencyExchange
 *
 * @group Currency
 */
class CurrencyExchangeTest extends UnitTestCase {

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheContextsManager;

  /**
   * The exchange rate provider.
   *
   * @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $exchangeRateProvider;

  /**
   * The input parser.
   *
   * @var \Commercie\Currency\InputInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $input;

  /**
   * The plugin definiton.
   *
   * @var mixed[]
   */
  protected $pluginDefinition;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Plugin\Filter\CurrencyExchange|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $this->pluginDefinition = [
      'cache' => TRUE,
      'provider' => $this->randomMachineName(),
    ];

    $this->cacheContextsManager = $this->getMockBuilder(CacheContextsManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->cacheContextsManager->expects($this->any())
      ->method('assertValidTokens')
      ->willReturn(TRUE);

    $this->exchangeRateProvider = $this->createMock(ExchangeRateProviderInterface::class);

    $this->input = $this->createMock(InputInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $container = new Container();
    $container->set('cache_contexts_manager', $this->cacheContextsManager);
    \Drupal::setContainer($container);

    $this->sut = new CurrencyExchange($configuration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->exchangeRateProvider, $this->input);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->createMock(ContainerInterface::class);
    $map = [
      ['currency.input', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->input],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
      ['currency.exchange_rate_provider', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->exchangeRateProvider],
    ];
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = CurrencyExchange::create($container, [], '', $this->pluginDefinition);
    $this->assertInstanceOf(CurrencyExchange::class, $sut);
  }

  /**
   * @covers ::process
   * @covers ::processCallback
   */
  public function testProcess() {
    $cache_contexts = Cache::mergeContexts(['baz', 'qux']);
    $cache_tags = Cache::mergeTags(['foo', 'bar']);

    $currency_code_from = 'EUR';
    $currency_code_to = 'NLG';
    $rate = '2.20371';
    $exchange_rate_provider_id = 'foo_bar';
    $exchange_rate = new ExchangeRate($currency_code_from, $currency_code_to, $rate, $exchange_rate_provider_id);
    $exchange_rate->addCacheContexts($cache_contexts);
    $exchange_rate->addCacheTags($cache_tags);

    $this->input->expects($this->any())
      ->method('parseAmount')
      ->will($this->returnArgument(0));

    $this->exchangeRateProvider->expects($this->any())
      ->method('load')
      ->with($currency_code_from, $currency_code_to)
      ->willReturn($exchange_rate);

    $langcode = $this->randomMachineName(2);

    $tokens_valid = [
      '[currency:EUR:NLG]' => '2.20371',
      '[currency:EUR:NLG:1]' => '2.20371',
      '[currency:EUR:NLG:2]' => '4.40742',
    ];
    $tokens_invalid = [
      // Missing arguments.
      '[currency]',
      '[currency:]',
      '[currency::]',
      '[currency:EUR]',
      // Invalid currency code.
      '[currency:EUR:123]',
      '[currency:123:EUR]',
      // Invalid currency code and missing argument.
      '[currency:123]',
    ];

    foreach ($tokens_valid as $token => $replacement) {
      $result = $this->sut->process($token, $langcode);
      $this->assertInstanceOf(FilterProcessResult::class, $result);
      $this->assertSame(round($replacement, 4), round($result->getProcessedText(), 4));
      $this->assertSame($cache_contexts, $result->getCacheContexts());
      $this->assertSame($cache_tags, $result->getCacheTags());
    }
    foreach ($tokens_invalid as $token) {
      $result = $this->sut->process($token, $langcode);
      $this->assertInstanceOf(FilterProcessResult::class, $result);
      $this->assertSame($token, $result->getProcessedText());
      $this->assertEmpty($result->getCacheContexts());
      $this->assertEmpty($result->getCacheTags());
    }
  }

  /**
   * @covers ::tips
   */
  public function testTips() {
    $tips = $this->sut->tips();
    $this->assertInstanceOf(TranslatableMarkup::class, $tips);
  }
}
