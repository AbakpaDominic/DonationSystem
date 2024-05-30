<?php

namespace Drupal\Tests\currency\Unit\Controller;

use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\currency\Controller\AddCurrency;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Controller\AddCurrency
 *
 * @group Currency
 */
class AddCurrencyTest extends UnitTestCase {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyStorage;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityFormBuilder;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Controller\AddCurrency
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->currencyStorage = $this->createMock(EntityStorageInterface::class);

    $this->entityFormBuilder = $this->createMock(EntityFormBuilderInterface::class);

    $this->sut = new AddCurrency($this->entityFormBuilder, $this->currencyStorage);
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
    $map = array(
      array('entity.form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityFormBuilder),
      array('entity_type.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_type_manager),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = AddCurrency::create($container);
    $this->assertInstanceOf(AddCurrency::class, $sut);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $currency = $this->createMock(CurrencyInterface::class);

    $this->currencyStorage->expects($this->once())
      ->method('create')
      ->with(array())
      ->willReturn($currency);

    $form = $this->createMock(EntityFormInterface::class);

    $this->entityFormBuilder->expects($this->once())
      ->method('getForm')
      ->with($currency)
      ->willReturn($form);

    $this->assertSame($form, $this->sut->execute());
  }

}
