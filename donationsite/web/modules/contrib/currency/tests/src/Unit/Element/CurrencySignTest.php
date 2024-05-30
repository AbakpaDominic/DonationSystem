<?php

namespace Drupal\Tests\currency\Unit\Element;

use Commercie\Currency\InputInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\currency\Element\CurrencySign;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Element\CurrencySign
 *
 * @group Currency
 */
class CurrencySignTest extends UnitTestCase {

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
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Element\CurrencySign
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->currencyStorage = $this->createMock(EntityStorageInterface::class);

    $this->input = $this->createMock(InputInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];

    $this->sut = new CurrencySign($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->currencyStorage, $this->input);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->expects($this->once())
      ->method('getStorage')
      ->with('currency')
      ->willReturn($this->currencyStorage);

    $container = $this->createMock(ContainerInterface::class);
    $map = array(
      array(
        'entity_type.manager',
        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
        $entity_type_manager
      ),
      array(
        'string_translation',
        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
        $this->stringTranslation
      ),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();

    $sut = CurrencySign::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(CurrencySign::class, $sut);
  }

  /**
   * @covers ::getInfo
   */
  public function testGetInfo() {
    $info = $this->sut->getInfo();
    $this->assertIsArray($info);
    foreach ($info['#element_validate'] as $callback) {
      $this->assertTrue(is_callable($callback));
    }
    foreach ($info['#process'] as $callback) {
      $this->assertTrue(is_callable($callback));
    }
  }

}
