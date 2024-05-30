<?php

namespace Drupal\Tests\currency\Unit\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\currency\Controller\EditCurrency;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Controller\EditCurrency
 *
 * @group Currency
 */
class EditCurrencyTest extends UnitTestCase {

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Controller\EditCurrency
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new EditCurrency($this->stringTranslation);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->createMock(ContainerInterface::class );
    $map = array(
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $form = EditCurrency::create($container);
    $this->assertInstanceOf(EditCurrency::class, $form);
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $label = $this->randomMachineName();
    $string = 'Edit @label';

    $currency = $this->createMock(CurrencyInterface::class);
    $currency->expects($this->once())
      ->method('label')
      ->willReturn($label);

    $this->assertInstanceOf(TranslatableMarkup::class, $this->sut->title($currency));
  }

}
