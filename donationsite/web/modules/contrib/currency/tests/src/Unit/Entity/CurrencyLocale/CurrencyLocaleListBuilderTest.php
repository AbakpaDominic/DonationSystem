<?php

namespace Drupal\Tests\currency\Unit\Entity\CurrencyLocale;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleListBuilder;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleListBuilder
 *
 * @group Currency
 */
class CurrencyLocaleListBuilderTest extends UnitTestCase {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityStorage;

  /**
   * The entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityType;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleListBuilder
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->entityStorage = $this->createMock(EntityStorageInterface::class);

    $this->entityType = $this->createMock(EntityTypeInterface::class);

    $this->moduleHandler = $this->createMock(ModuleHandlerInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new CurrencyLocaleListBuilder($this->entityType, $this->entityStorage, $this->stringTranslation, $this->moduleHandler);
  }

  /**
   * @covers ::createInstance
   * @covers ::__construct
   */
  function testCreateInstance() {
    $this->entityType->expects($this->any())
      ->method('id')
      ->willReturn('currency');

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->expects($this->once())
      ->method('getStorage')
      ->with('currency')
      ->willReturn($this->entityStorage);

    $container = $this->createMock(ContainerInterface::class);
    $map = array(
      array('entity_type.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_type_manager),
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = CurrencyLocaleListBuilder::createInstance($container, $this->entityType);
    $this->assertInstanceOf(CurrencyLocaleListBuilder::class, $sut);
  }

  /**
   * @covers ::buildHeader
   */
  function testBuildHeader() {
    $header = $this->sut->buildHeader();
    foreach ($header as $cell) {
      $this->assertInstanceOf(TranslatableMarkup::class, $cell);
    }
  }

  /**
   * @covers ::buildRow
   */
  function testBuildRow() {
    $entity_label = $this->randomMachineName();

    $currency_locale = $this->createMock(CurrencyLocaleInterface::class);
    $currency_locale->expects($this->any())
      ->method('label')
      ->willReturn($entity_label);

    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn([]);

    $row = $this->sut->buildRow($currency_locale);
    $expected = array(
      'label' => $entity_label,
      'operations' => array(
        'data' => array(
          '#type' => 'operations',
          '#links' => array(),
        ),
      ),
    );
    $this->assertSame($expected, $row);
  }

}
