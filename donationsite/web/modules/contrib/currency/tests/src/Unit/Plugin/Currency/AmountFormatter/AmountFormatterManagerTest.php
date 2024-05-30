<?php

namespace Drupal\Tests\currency\Unit\Plugin\Currency\AmountFormatter;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManager
 *
 * @group Currency
 */
class AmountFormatterManagerTest extends UnitTestCase {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\https://github.com/Commercie/currency/pull/6
   */
  protected $cache;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The plugin discovery.
   *
   * @var \Drupal\Component\Plugin\Discovery\DiscoveryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $discovery;

  /**
   * The plugin factory.
   *
   * @var \Drupal\Component\Plugin\Factory\DefaultFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $factory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManager
   */
  public $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->discovery = $this->createMock(DiscoveryInterface::class);

    $this->factory = $this->getMockBuilder(DefaultFactory::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->moduleHandler = $this->createMock(ModuleHandlerInterface::class);

    $this->cache = $this->createMock(CacheBackendInterface::class);

    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);

    $namespaces = new \ArrayObject();

    $this->sut = new AmountFormatterManager($namespaces, $this->cache, $this->moduleHandler, $this->configFactory);
    $discovery_property = new \ReflectionProperty($this->sut, 'discovery');
    $discovery_property->setAccessible(TRUE);
    $discovery_property->setValue($this->sut, $this->discovery);
    $factory_property = new \ReflectionProperty($this->sut, 'factory');
    $factory_property->setAccessible(TRUE);
    $factory_property->setValue($this->sut, $this->factory);
  }

  /**
   * @covers ::getFallbackPluginId
   */
  public function testGetFallbackPluginId() {
    $plugin_id = $this->randomMachineName();
    $plugin_configuration = array($this->randomMachineName());
    $this->assertIsString($this->sut->getFallbackPluginId($plugin_id, $plugin_configuration));
  }

  /**
   * @covers ::getDefaultPluginId
   */
  public function testGetDefaultPluginId() {
    $plugin_id = $this->randomMachineName();

    $config = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->once())
      ->method('get')
      ->with('plugin_id')
      ->willReturn($plugin_id);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('currency.amount_formatting')
      ->willReturn($config);

    $this->assertSame($plugin_id, $this->sut->getDefaultPluginId());
  }

  /**
   * @covers ::setDefaultPluginId
   */
  public function testSetDefaultPluginId() {
    $plugin_id = $this->randomMachineName();

    $config = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->once())
      ->method('set')
      ->with('plugin_id', $plugin_id)
      ->will($this->returnSelf());
    $config->expects($this->once())
      ->method('save');

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('currency.amount_formatting')
      ->willReturn($config);

    $this->assertSame(spl_object_hash($this->sut), spl_object_hash($this->sut->setDefaultPluginId($plugin_id)));
  }

  /**
   * @covers ::getDefaultPlugin
   */
  public function testGetDefaultPlugin() {
    $namespaces = new \ArrayObject();

    $default_plugin_id = $this->randomMachineName();

    $formatter = $this->createMock(AmountFormatterInterface::class);

    /** @var \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManager|\PHPUnit\Framework\MockObject\MockObject $currency_amount_formatter_manager */
    $currency_amount_formatter_manager = $this->getMockBuilder(AmountFormatterManager::class)
    ->setConstructorArgs(array($namespaces, $this->cache, $this->moduleHandler, $this->configFactory))
    ->setMethods(array('getDefaultPluginId', 'createInstance'))
    ->getMock();
    $currency_amount_formatter_manager->expects($this->once())
      ->method('getDefaultPluginId')
      ->willReturn($default_plugin_id);
    $currency_amount_formatter_manager->expects($this->once())
      ->method('createInstance')
      ->with($default_plugin_id)
      ->willReturn($formatter);

    $this->assertSame(spl_object_hash($formatter), spl_object_hash($currency_amount_formatter_manager->getDefaultPlugin()));
  }
}
