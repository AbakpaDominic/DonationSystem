<?php

namespace Drupal\Tests\currency\Unit\Plugin\Filter;

use Commercie\Currency\InputInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\currency\Plugin\Filter\CurrencyLocalize;
use Drupal\filter\FilterProcessResult;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\Filter\CurrencyLocalize
 *
 * @group Currency
 */
class CurrencyLocalizeTest extends UnitTestCase {

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheContextsManager;

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyStorage;

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
   * @var \Drupal\currency\Plugin\Filter\CurrencyLocalize
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

    $this->currencyStorage = $this->createMock(EntityStorageInterface::class);

    $this->input = $this->createMock(InputInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $container = new Container();
    $container->set('cache_contexts_manager', $this->cacheContextsManager);
    \Drupal::setContainer($container);

    $this->sut = new CurrencyLocalize($configuration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->currencyStorage, $this->input);
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
    $map = [
      ['entity_type.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_type_manager],
      ['currency.input', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->input],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = CurrencyLocalize::create($container, [], '', $this->pluginDefinition);
    $this->assertInstanceOf(CurrencyLocalize::class, $sut);
  }

  /**
   * @covers ::process
   * @covers ::processCallback
   */
  function testProcess() {
    $cache_contexts = Cache::mergeContexts(['baz', 'qux']);
    $cache_tags = Cache::mergeTags(['foo', 'bar']);

    $map = [
      ['100', TRUE, LanguageInterface::TYPE_CONTENT, '€100.00'],
      ['100.7654', TRUE, LanguageInterface::TYPE_CONTENT, '€100.77'],
      ['1.99', TRUE, LanguageInterface::TYPE_CONTENT, '€1.99'],
      ['2.99', TRUE, LanguageInterface::TYPE_CONTENT, '€2.99'],
    ];
    $currency = $this->createMock(CurrencyInterface::class);
    $currency->expects($this->any())
      ->method('formatAmount')
      ->willReturnMap($map);
    $currency->expects($this->atLeastOnce())
      ->method('getCacheContexts')
      ->willReturn($cache_contexts);
    $currency->expects($this->atLeastOnce())
      ->method('getCacheTags')
      ->willReturn($cache_tags);

    $this->currencyStorage->expects($this->any())
      ->method('load')
      ->with('EUR')
      ->willReturn($currency);

    $this->input->expects($this->any())
      ->method('parseAmount')
      ->will($this->returnArgument(0));

    $langcode = $this->randomMachineName(2);

    $tokens_valid = [
      '[currency-localize:EUR:100]' => '€100.00',
      '[currency-localize:EUR:100.7654]' => '€100.77',
      '[currency-localize:EUR:1.99]' => '€1.99',
      '[currency-localize:EUR:2.99]' => '€2.99',
    ];
    $tokens_invalid = [
      // Missing arguments.
      '[currency-localize]',
      '[currency-localize:]',
      '[currency-localize::]',
      '[currency-localize:EUR]',
      // Invalid currency code.
      '[currency-localize:123:456]',
      // Invalid currency code and missing argument.
      '[currency-localize:123]',
    ];

    foreach ($tokens_valid as $token => $replacement) {
      $result = $this->sut->process($token, $langcode);
      $this->assertInstanceOf(FilterProcessResult::class, $result);
      $this->assertSame($replacement, $result->getProcessedText());
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
