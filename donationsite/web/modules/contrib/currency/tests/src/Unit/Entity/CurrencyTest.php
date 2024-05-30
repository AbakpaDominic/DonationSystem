<?php

namespace Drupal\Tests\currency\Unit\Entity;

use Commercie\Currency\Usage;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\currency\Entity\Currency;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\Entity\Currency
 *
 * @group Currency
 */
class CurrencyTest extends UnitTestCase {

  /**
   * The currency amount formatter manager.
   *
   * @var \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyAmountFormatterManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Entity\Currency
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  function setUp(): void {
    $this->entityTypeId = $this->randomMachineName();

    $this->currencyAmountFormatterManager = $this->createMock(AmountFormatterManagerInterface::class);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);

    $this->sut = new Currency([], $this->entityTypeId);
    $this->sut->setCurrencyAmountFormatterManager($this->currencyAmountFormatterManager);
    $this->sut->setEntityTypeManager($this->entityTypeManager);
  }

  /**
   * @covers ::setCurrencyAmountFormatterManager
   * @covers ::getCurrencyAmountFormatterManager
   */
  public function testGetCurrencyAmountFormatterManager() {
    $method = new \ReflectionMethod($this->sut, 'getCurrencyAmountFormatterManager');
    $method->setAccessible(TRUE);

    $this->assertSame($this->sut, $this->sut->setCurrencyAmountFormatterManager($this->currencyAmountFormatterManager));
    $this->assertSame($this->currencyAmountFormatterManager, $method->invoke($this->sut));
  }

  /**
   * @covers ::setEntityTypeManager
   * @covers ::entityTypeManager
   */
  public function testEntityTypeManager() {
    $method = new \ReflectionMethod($this->sut, 'entityTypeManager');
    $method->setAccessible(TRUE);

    $this->assertSame($this->sut, $this->sut->setEntityTypeManager($this->entityTypeManager));
    $this->assertSame($this->entityTypeManager, $method->invoke($this->sut));
  }

  /**
   * @covers ::getRoundingStep
   * @covers ::setRoundingStep
   */
  function testGetRoundingStep() {
    $rounding_step = mt_rand();

    $this->assertSame($this->sut, $this->sut->setRoundingStep($rounding_step));
    $this->assertSame($rounding_step, $this->sut->getRoundingStep());
  }

  /**
   * @covers ::getRoundingStep
   */
  function testGetRoundingStepBySubunits() {
    $subunits = 5;
    $rounding_step = '0.200000';

    $this->sut->setSubunits($subunits);

    $this->assertSame($rounding_step, $this->sut->getRoundingStep());
  }

  /**
   * @covers ::getRoundingStep
   */
  function testGetRoundingStepUnavailable() {
    $this->assertNull($this->sut->getRoundingStep());
  }

  /**
   * @covers ::formatAmount
   * @covers ::getCurrencyAmountFormatterManager
   *
   * @depends testGetRoundingStep
   *
   * @dataProvider providerTestFormatAmount
   */
  function testFormatAmount($expected, $amount, $amount_with_currency_precision_applied) {
    $amount_formatter = $this->createMock(AmountFormatterInterface::class);
    $amount_formatter->expects($this->atLeastOnce())
      ->method('formatAmount')
      ->with($this->sut, $amount_with_currency_precision_applied)
      ->willReturn($expected);

    $this->currencyAmountFormatterManager->expects($this->atLeastOnce())
      ->method('getDefaultPlugin')
      ->willReturn($amount_formatter);

    $this->sut->setCurrencyCode('BLA');
    $this->sut->setSubunits(100);

    $this->assertSame($expected, $this->sut->formatAmount($amount, $amount !== $amount_with_currency_precision_applied));
  }

  /**
   * Provides data to self::testFormatAmount().
   */
  public function providerTestFormatAmount() {
    return [
      ['BLA 12,345.68', '12345.6789', '12345.68'],
      ['BLA 12,345.6789', '12345.6789', '12345.6789'],
    ];
  }

  /**
   * @covers ::getDecimals
   */
  function testGetDecimals() {
    foreach ([1, 2, 3] as $decimals) {
      $this->sut->setSubunits(pow(10, $decimals));
      $this->assertSame($decimals, $this->sut->getDecimals());
    }
  }

  /**
   * @covers ::isObsolete
   */
  function testIsObsolete() {
    // A currency without usage data.
    $this->assertFalse($this->sut->isObsolete());

    // A currency that is no longer being used.
    $usage = new Usage();
    $usage->setStart('1813-01-01')
      ->setEnd('2002-02-28');
    $this->sut->setUsages([$usage]);
    $this->assertTrue($this->sut->isObsolete());

    // A currency that will become obsolete next year.
    $usage = new Usage();
    $usage->setStart('1813-01-01')
      ->setEnd(date('o') + 1 . '-02-28');
    $this->sut->setUsages([$usage]);
    $this->assertFalse($this->sut->isObsolete());
  }

  /**
   * @covers ::getAlternativeSigns
   * @covers ::setAlternativeSigns
   */
  function testGetAlternativeSigns() {
    $alternative_signs = ['A', 'B'];
    $this->assertSame($this->sut, $this->sut->setAlternativeSigns($alternative_signs));
    $this->assertSame($alternative_signs, $this->sut->getAlternativeSigns());
  }

  /**
   * @covers ::id
   * @covers ::setCurrencyCode
   */
  function testId() {
    $currency_code = $this->randomMachineName(3);
    $this->assertSame($this->sut, $this->sut->setCurrencyCode($currency_code));
    $this->assertSame($currency_code, $this->sut->id());
  }

  /**
   * @covers ::getCurrencyCode
   * @covers ::setCurrencyCode
   */
  function testGetCurrencyCode() {
    $currency_code = $this->randomMachineName(3);
    $this->assertSame($this->sut, $this->sut->setCurrencyCode($currency_code));
    $this->assertSame($currency_code, $this->sut->getCurrencyCode());
  }

  /**
   * @covers ::getCurrencyNumber
   * @covers ::setCurrencyNumber
   */
  function testGetCurrencyNumber() {
    $currency_number = '000';
    $this->assertSame($this->sut, $this->sut->setCurrencyNumber($currency_number));
    $this->assertSame($currency_number, $this->sut->getCurrencyNumber());
  }

  /**
   * @covers ::label
   * @covers ::setLabel
   */
  function testLabel() {
    $entity_type = $this->createMock(EntityTypeInterface::class);
    $entity_type->expects($this->atLeastOnce())
      ->method('getKey')
      ->with('label')
      ->willReturn('label');

    $this->entityTypeManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->willReturn($entity_type);

    $label = $this->randomMachineName();
    $this->assertSame($this->sut, $this->sut->setLabel($label));
    $this->assertSame($label, $this->sut->label());
  }

  /**
   * @covers ::getSign
   * @covers ::setSign
   */
  function testGetSign() {
    $sign = $this->randomMachineName(1);
    $this->assertSame($this->sut, $this->sut->setSign($sign));
    $this->assertSame($sign, $this->sut->getSign());
  }

  /**
   * @covers ::setSubunits
   * @covers ::getSubunits
   */
  function testGetSubunits() {
    $subunits = 73;
    $this->assertSame($this->sut, $this->sut->setSubunits($subunits));
    $this->assertSame($subunits, $this->sut->getSubunits());
  }

  /**
   * @covers ::setUsages
   * @covers ::getUsages
   */
  function testGetUsage() {
    $usage = new Usage();
    $usage->setStart('1813-01-01')
    ->setEnd(date('o') + 1 . '-02-28');
    $this->assertSame($this->sut, $this->sut->setUsages([$usage]));
    $this->assertSame([$usage], $this->sut->getUsages());
  }

}
