<?php

namespace Drupal\Tests\currency\Unit\Plugin\views\filter;

use Drupal\currency\FormHelperInterface;
use Drupal\currency\Plugin\views\filter\Currency;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\views\filter\Currency
 *
 * @group Currency
 */
class CurrencyTest extends UnitTestCase {

  /**
   * The form helper
   *
   * @var \Drupal\currency\FormHelperInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $formHelper;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Plugin\views\field\Currency
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];

    $this->formHelper = $this->createMock(FormHelperInterface::class);

    $this->sut = new Currency($configuration, $plugin_id, $plugin_definition, $this->formHelper);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->createMock(ContainerInterface::class);
    $map = [
      ['currency.form_helper', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->formHelper],
    ];
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];

    $sut = Currency::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(Currency::class, $sut);
  }

  /**
   * @covers ::getValueOptions
   */
  public function testGetValueOptions() {
    $options = array(
      $this->randomMachineName() => $this->randomMachineName(),
    );

    $this->formHelper->expects($this->atLeastOnce())
      ->method('getCurrencyOptions')
      ->willReturn($options);

    $method = new \ReflectionMethod($this->sut, 'getValueOptions');
    $method->setAccessible(TRUE);

    $this->assertSame($options, $method->invoke($this->sut));
  }

}
