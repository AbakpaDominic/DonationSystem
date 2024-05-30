<?php

namespace Drupal\Tests\currency\Unit\Plugin\views\field {

  use Drupal\Core\Config\Config;
  use Drupal\Core\Config\ConfigFactoryInterface;
  use Drupal\Core\Entity\EntityTypeManagerInterface;
  use Drupal\Core\Entity\EntityStorageInterface;
  use Drupal\Core\Extension\ModuleHandlerInterface;
  use Drupal\Core\Form\FormState;
  use Drupal\Core\Render\RendererInterface;
  use Drupal\currency\Entity\CurrencyInterface;
  use Drupal\currency\Plugin\views\field\Amount;
  use Drupal\Tests\UnitTestCase;
  use Drupal\views\Plugin\views\display\DisplayPluginBase;
  use Drupal\views\Plugin\views\query\Sql;
  use Drupal\views\ResultRow;
  use Drupal\views\ViewExecutable;
  use RuntimeException;
  use Symfony\Component\DependencyInjection\ContainerBuilder;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\currency\Plugin\views\field\Amount
   *
   * @group Currency
   */
  class AmountTest extends UnitTestCase {

    /**
     * The config factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configFactory;

    /**
     * The currency storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $currencyStorage;

    /**
     * The module handler.
     *
     * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleHandler;

    /**
     * The plugin definiton.
     *
     * @var mixed[]
     */
    protected $pluginDefinition;

    /**
     * The renderer.
     *
     * @var \Drupal\Core\Render\RendererInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $renderer;

    /**
     * The string translator.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stringTranslation;

    /**
     * The class under test.
     *
     * @var \Drupal\currency\Plugin\views\field\Amount
     */
    protected $sut;

    /**
     * The Views display handler.
     *
     * @param \Drupal\views\Plugin\views\display\DisplayPluginBase|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewsDisplayHandler;

    /**
     * The Views view executable.
     *
     * @param \Drupal\views\ViewExecutable|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewsViewExecutable;

    /**
     * The Views query.
     *
     * @var \Drupal\views\Plugin\views\query\Sql
     */
    protected $viewsQuery;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void {
      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $this->pluginDefinition = [
        'group' => $this->randomMachineName(),
        'title' => $this->randomMachineName(),
      ];

      $this->configFactory = $this->createMock(ConfigFactoryInterface::class);

      $this->currencyStorage = $this->createMock(EntityStorageInterface::class);

      $this->moduleHandler = $this->createMock(ModuleHandlerInterface::class);

      $this->renderer = $this->createMock(RendererInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->viewsDisplayHandler = $this->getMockBuilder(DisplayPluginBase::class)
        ->disableOriginalConstructor()
        ->getMock();

      $this->viewsQuery = $this->getMockBuilder(Sql::class)
        ->disableOriginalConstructor()
        ->getMock();

      $this->viewsViewExecutable = $this->getMockBuilder(ViewExecutable::class)
        ->disableOriginalConstructor()
        ->getMock();
      $this->viewsViewExecutable->display_handler = $this->viewsDisplayHandler;
      $this->viewsViewExecutable->query = $this->viewsQuery;

      $container = new ContainerBuilder();
      $container->set('config.factory', $this->configFactory);
      $container->set('renderer', $this->renderer);
      \Drupal::setContainer($container);

      $this->sut = new Amount($configuration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->moduleHandler, $this->renderer, $this->currencyStorage);
      $this->sut->init($this->viewsViewExecutable, $this->viewsDisplayHandler);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function testCreate() {
      $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
      $entity_type_manager->expects($this->atLeastOnce())
        ->method('getStorage')
        ->with('currency')
        ->willReturn($this->currencyStorage);

      $container = $this->createMock(ContainerInterface::class);
      $map = [
        ['entity_type.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_type_manager],
        ['module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler],
        ['renderer', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->renderer],
        ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
      ];
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $sut = Amount::create($container, [], '', $this->pluginDefinition);
      $this->assertInstanceOf(Amount::class, $sut);
    }

    /**
     * @covers ::defineOptions
     */
    public function testDefineOptions() {
      foreach ($this->sut->defineOptions() as $option) {
        $this->assertIsArray($option);
        $this->assertTrue(array_key_exists('default', $option) || array_key_exists('contains', $option));
      }
    }

    /**
     * @covers ::buildOptionsForm
     */
    public function testBuildOptionsForm() {
      $this->viewsDisplayHandler->expects($this->atLeastOnce())
        ->method('getFieldLabels')
        ->willReturn([]);
      $this->viewsDisplayHandler->expects($this->atLeastOnce())
        ->method('getHandlers')
        ->with('argument')
        ->willReturn([]);

      $views_settings_config = $this->getMockBuilder(Config::class)
        ->disableOriginalConstructor()
        ->getMock();
      $views_settings_config->expects($this->atLeastOnce())
        ->method('get')
        ->with('field_rewrite_elements')
        ->willReturn([]);

      $this->configFactory->expects($this->atLeastOnce())
        ->method('get')
        ->with('views.settings')
        ->willReturn($views_settings_config);

      $container = new ContainerBuilder();
      $container->set('config.factory', $this->configFactory);
      $container->set('string_translation', $this->stringTranslation);
      \Drupal::setContainer($container);

      $form = [];
      $form_state = new FormState();
      $this->sut->buildOptionsForm($form, $form_state);
      foreach ($form as $element) {
        $this->assertIsArray($element);
      }
    }

    /**
     * @covers ::defaultDefinition
     */
    function testDefaultDefinition() {
      $method = new \ReflectionMethod($this->sut, 'defaultDefinition');
      $method->setAccessible(TRUE);

      $this->assertIsArray($method->invoke($this->sut));
    }

    /**
     * @covers ::getAmount
     */
    function testGetAmount() {
      $amount = mt_rand();

      $field_alias = $this->randomMachineName();

      $this->sut->field_alias = $field_alias;

      $result_row = new ResultRow([
        $field_alias => $amount,
      ]);

      $method = new \ReflectionMethod($this->sut, 'getAmount');
      $method->setAccessible(TRUE);

      $this->assertSame($amount, $method->invoke($this->sut, $result_row));
    }

    /**
     * @covers ::getCurrency
     */
    function testGetCurrencyWithoutLoadableCurrencies() {
      $this->expectException(RuntimeException::class);
      $this->currencyStorage->expects($this->atLeastOnce())
        ->method('load')
        ->willReturn(NULL);

      $result_row = new ResultRow();

      $method = new \ReflectionMethod($this->sut, 'getCurrency');
      $method->setAccessible(TRUE);

      $method->invoke($this->sut, $result_row);
    }

    /**
     * @covers ::getCurrency
     */
    function testGetCurrencyWithFallbackCurrency() {
      $currency = $this->createMock(CurrencyInterface::class);

      $map = [
        ['XXX', $currency],
      ];
      $this->currencyStorage->expects($this->atLeastOnce())
        ->method('load')
        ->willReturnMap($map);

      $result_row = new ResultRow();

      $method = new \ReflectionMethod($this->sut, 'getCurrency');
      $method->setAccessible(TRUE);

      $this->assertSame($currency, $method->invoke($this->sut, $result_row));
    }

    /**
     * @covers ::getCurrency
     */
    function testGetCurrencyWithFixedCurrency() {
      $currency_code = $this->randomMachineName();

      $currency = $this->createMock(CurrencyInterface::class);

      $map = [
        [$currency_code, $currency],
      ];
      $this->currencyStorage->expects($this->atLeastOnce())
        ->method('load')
        ->willReturnMap($map);

      $result_row = new ResultRow();

      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $this->pluginDefinition['currency_code'] = $currency_code;

      $this->sut = new Amount($configuration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->moduleHandler, $this->renderer, $this->currencyStorage);
      $this->sut->init($this->viewsViewExecutable, $this->viewsDisplayHandler);

      $method = new \ReflectionMethod($this->sut, 'getCurrency');
      $method->setAccessible(TRUE);

      $this->assertSame($currency, $method->invoke($this->sut, $result_row));
    }

    /**
     * @covers ::getCurrency
     */
    function testGetCurrencyWithCurrencyCodeField() {
      $currency_code = $this->randomMachineName();

      $currency_code_field = $this->randomMachineName();

      $currency = $this->createMock(CurrencyInterface::class);

      $map = [
        [$currency_code, $currency],
      ];
      $this->currencyStorage->expects($this->atLeastOnce())
        ->method('load')
        ->willReturnMap($map);

      $field_alias = $this->randomMachineName();

      $result_row = new ResultRow([
        $field_alias => $currency_code,
      ]);

      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $this->pluginDefinition['currency_code_field'] = $currency_code_field;

      $this->sut = new Amount($configuration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->moduleHandler, $this->renderer, $this->currencyStorage);
      $this->sut->init($this->viewsViewExecutable, $this->viewsDisplayHandler);
      $this->sut->aliases['currency_code_field'] = $field_alias;

      $method = new \ReflectionMethod($this->sut, 'getCurrency');
      $method->setAccessible(TRUE);

      $this->assertSame($currency, $method->invoke($this->sut, $result_row));
    }

    /**
     * @covers ::render
     *
     * @dataProvider providerTestRender
     *
     * @depends testGetAmount
     * @depends testGetCurrencyWithoutLoadableCurrencies
     * @depends testGetCurrencyWithFallbackCurrency
     * @depends testGetCurrencyWithFixedCurrency
     * @depends testGetCurrencyWithCurrencyCodeField
     */
    function testRender($round) {
      $amount = mt_rand();

      $formatted_amount = $this->randomMachineName();

      $field_alias = $this->randomMachineName();

      $currency_code = $this->randomMachineName();

      $currency = $this->createMock(CurrencyInterface::class);
      $currency->expects($this->atLeastOnce())
        ->method('formatAmount')
        ->with($amount, $round)
        ->willReturn($formatted_amount);

      $this->currencyStorage->expects($this->atLeastOnce())
        ->method('load')
        ->with($currency_code)
        ->willReturn($currency);

      $result_row = new ResultRow([
        $field_alias => $amount,
      ]);

      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $this->pluginDefinition['currency_code'] = $currency_code;

      $options = [
        'currency_round' => $round,
      ];

      $this->sut = new Amount($configuration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->moduleHandler, $this->renderer, $this->currencyStorage);
      $this->sut->init($this->viewsViewExecutable, $this->viewsDisplayHandler, $options);
      $this->sut->field_alias = $field_alias;

      $this->assertSame($formatted_amount, $this->sut->render($result_row));
    }

    /**
     * Provides data to self::testRender().
     */
    public function providerTestRender() {
      return [
        [TRUE],
        [FALSE],
      ];
    }

  }

}

namespace {

  if (!function_exists('t')) {
    function t() {
    }
  }

}
