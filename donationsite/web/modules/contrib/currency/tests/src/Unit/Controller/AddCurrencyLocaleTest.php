<?php

namespace Drupal\Tests\currency\Unit\Controller;

use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\currency\Controller\AddCurrencyLocale;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Controller\AddCurrencyLocale
 *
 * @group Currency
 */
class AddCurrencyLocaleTest extends UnitTestCase {

  /**
   * The currency locale storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyLocaleStorage;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityFormBuilder;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Controller\AddCurrencyLocale
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->currencyLocaleStorage = $this->createMock(EntityStorageInterface::class);

    $this->entityFormBuilder = $this->createMock(EntityFormBuilderInterface::class);

    $this->sut = new AddCurrencyLocale($this->entityFormBuilder, $this->currencyLocaleStorage);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->expects($this->atLeastOnce())
      ->method('getStorage')
      ->with('currency_locale')
      ->willReturn($this->currencyLocaleStorage);

    $container = $this->createMock(ContainerInterface::class);
    $map = array(
      array('entity.form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityFormBuilder),
      array('entity_type.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_type_manager),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = AddCurrencyLocale::create($container);
    $this->assertInstanceOf(AddCurrencyLocale::class, $sut);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $currency_locale = $this->createMock(CurrencyLocaleInterface::class);

    $this->currencyLocaleStorage->expects($this->once())
      ->method('create')
      ->with(array())
      ->willReturn($currency_locale);

    $form = $this->createMock(EntityFormInterface::class);

    $this->entityFormBuilder->expects($this->once())
      ->method('getForm')
      ->with($currency_locale)
      ->willReturn($form);

    $this->assertSame($form, $this->sut->execute());
  }

}
