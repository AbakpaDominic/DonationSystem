<?php

namespace Drupal\Tests\currency\Unit;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Drupal\currency\FormHelper;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\FormHelper
 *
 * @group Currency
 */
class FormHelperTest extends UnitTestCase {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyStorage;

  /**
   * The currency locale storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyLocaleStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\FormHelper
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->currencyStorage = $this->createMock(EntityStorageInterface::class);

    $this->currencyLocaleStorage = $this->createMock(EntityStorageInterface::class);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $map = [
      ['currency', $this->currencyStorage],
      ['currency_locale', $this->currencyLocaleStorage],
    ];
    $this->entityTypeManager->expects($this->atLeastOnce())
      ->method('getStorage')
      ->willReturnMap($map);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new FormHelper($this->stringTranslation, $this->entityTypeManager);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new FormHelper($this->stringTranslation, $this->entityTypeManager);
  }

  /**
   * @covers ::getCurrencyOptions
   */
  public function testCurrencyOptionsWithoutLimitation() {
    $this->currencyStorage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([]);

    $this->assertSame([], $this->sut->getCurrencyOptions(NULL));
  }

  /**
   * @covers ::getCurrencyOptions
   */
  public function testCurrencyOptionsWithLimitation() {
    $this->currencyStorage->expects($this->never())
      ->method('loadMultiple');

    $currency_locale_id_a = $this->randomMachineName();
    $currency_locale_label_a = $this->randomMachineName();
    $currency_locale_a = $this->createMock(CurrencyInterface::class);
    $currency_locale_a->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($currency_locale_id_a);
    $currency_locale_a->expects($this->atLeastOnce())
      ->method('label')
      ->willReturn($currency_locale_label_a);
    $currency_locale_a->expects($this->atLeastOnce())
      ->method('status')
      ->willReturn(TRUE);
    $currency_locale_b = $this->createMock(CurrencyInterface::class);
    $currency_locale_b->expects($this->atLeastOnce())
      ->method('status')
      ->willReturn(FALSE);
    $currency_locale_id_c = $this->randomMachineName();
    $currency_locale_label_c = $this->randomMachineName();
    $currency_locale_c = $this->createMock(CurrencyInterface::class);
    $currency_locale_c->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($currency_locale_id_c);
    $currency_locale_c->expects($this->atLeastOnce())
      ->method('label')
      ->willReturn($currency_locale_label_c);
    $currency_locale_c->expects($this->atLeastOnce())
      ->method('status')
      ->willReturn(TRUE);

    $expected_options = [
      $currency_locale_id_a => $currency_locale_label_a . ' (' . $currency_locale_id_a . ')',
      $currency_locale_id_c => $currency_locale_label_c . ' (' . $currency_locale_id_c . ')',
    ];
    natcasesort($expected_options);

    $options = $this->sut->getCurrencyOptions([$currency_locale_a, $currency_locale_b, $currency_locale_c]);

    $this->assertEmpty(array_diff_key($options, $expected_options));
    $this->assertEmpty(array_diff_key($expected_options, $options));
    foreach ($options as $option) {
      $this->assertInstanceOf(TranslatableMarkup::class, $option);
    }
  }

  /**
   * @covers ::getCurrencyLocaleOptions
   */
  public function testCurrencyLocaleOptionsWithoutLimitation() {
    $this->currencyLocaleStorage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([]);

    $this->assertSame([], $this->sut->getCurrencyLocaleOptions(NULL));
  }

  /**
   * @covers ::getCurrencyLocaleOptions
   */
  public function testCurrencyLocaleOptionsWithLimitation() {
    $this->currencyStorage->expects($this->never())
      ->method('loadMultiple');

    $currency_locale_id_a = $this->randomMachineName();
    $currency_locale_label_a = $this->randomMachineName();
    $currency_locale_a = $this->createMock(CurrencyLocaleInterface::class);
    $currency_locale_a->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($currency_locale_id_a);
    $currency_locale_a->expects($this->atLeastOnce())
      ->method('label')
      ->willReturn($currency_locale_label_a);
    $currency_locale_a->expects($this->atLeastOnce())
      ->method('status')
      ->willReturn(TRUE);
    $currency_locale_b = $this->createMock(CurrencyLocaleInterface::class);
    $currency_locale_b->expects($this->atLeastOnce())
      ->method('status')
      ->willReturn(FALSE);
    $currency_locale_id_c = $this->randomMachineName();
    $currency_locale_label_c = $this->randomMachineName();
    $currency_locale_c = $this->createMock(CurrencyLocaleInterface::class);
    $currency_locale_c->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($currency_locale_id_c);
    $currency_locale_c->expects($this->atLeastOnce())
      ->method('label')
      ->willReturn($currency_locale_label_c);
    $currency_locale_c->expects($this->atLeastOnce())
      ->method('status')
      ->willReturn(TRUE);

    $expected_options = [
      $currency_locale_id_a => $currency_locale_label_a,
      $currency_locale_id_c => $currency_locale_label_c,
    ];
    natcasesort($expected_options);

    $this->assertSame($expected_options, $this->sut->getCurrencyLocaleOptions([$currency_locale_a, $currency_locale_b, $currency_locale_c]));
  }

}
