<?php

namespace Drupal\Tests\currency\Unit\Element;

use Commercie\Currency\InputInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormState;
use Drupal\currency\Element\CurrencyAmount;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\currency\FormHelperInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Element\CurrencyAmount
 *
 * @group Currency
 */
class CurrencyAmountTest extends UnitTestCase {

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
   * The form helper.
   *
   * @var \Drupal\currency\FormHelperInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $formHelper;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Element\CurrencyAmount
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->currencyStorage = $this->createMock(EntityStorageInterface::class);

    $this->formHelper = $this->createMock(FormHelperInterface::class);

    $this->input = $this->createMock(InputInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];

    $this->sut = new CurrencyAmount($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->currencyStorage, $this->input, $this->formHelper);
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
        'currency.form_helper',
        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
        $this->formHelper
      ),
      array(
        'currency.input',
        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
        $this->input
      ),
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

    $sut = CurrencyAmount::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(CurrencyAmount::class, $sut);
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

  /**
   * @covers ::process
   *
   * @dataProvider providerTestProcessWithInvalidMinimumAmount
   */
  public function testProcessWithInvalidMinimumAmount($amount) {
    $this->expectException(\InvalidArgumentException::class);
    $element = [
      '#minimum_amount' => $amount,
      '#maximum_amount' => FALSE,
      '#limit_currency_codes' => [],
    ];

    $form_state = new FormState();
    $form = [];

    $this->sut->process($element, $form_state, $form);
  }

  /**
   * Provides data to self::testProcessWithInvalidMinimumAmount().
   */
  public function providerTestProcessWithInvalidMinimumAmount() {
    return [
      [TRUE],
      [NULL],
      [$this->randomMachineName()],
      [new \stdClass()],
    ];
  }

  /**
   * @covers ::process
   *
   * @dataProvider providerTestProcessWithInvalidMaximumAmount
   */
  public function testProcessWithInvalidMaximumAmount($amount) {
    $this->expectException(\InvalidArgumentException::class);
    $element = [
      '#minimum_amount' => FALSE,
      '#maximum_amount' => $amount,
      '#limit_currency_codes' => [],
    ];

    $form_state = new FormState();
    $form = [];

    $this->sut->process($element, $form_state, $form);
  }

  /**
   * Provides data to self::testProcessWithInvalidMaximumAmount().
   */
  public function providerTestProcessWithInvalidMaximumAmount() {
    return [
      [TRUE],
      [NULL],
      [$this->randomMachineName()],
      [new \stdClass()],
    ];
  }

  /**
   * @covers ::process
   *
   * @dataProvider providerTestProcessWithInvalidLimitedCurrencyCodes
   */
  public function testProcessWithInvalidLimitedCurrencyCodes($limit_currency_codes, array $default_value) {
    $this->expectException(\InvalidArgumentException::class);
    $element = [
      '#default_value' => $default_value,
      '#minimum_amount' => FALSE,
      '#maximum_amount' => FALSE,
      '#limit_currency_codes' => $limit_currency_codes,
    ];

    $form_state = new FormState();
    $form = [];

    $this->sut->process($element, $form_state, $form);
  }

  /**
   * Provides data to self::testProcessWithInvalidLimitedCurrencyCodes().
   */
  public function providerTestProcessWithInvalidLimitedCurrencyCodes() {
    $currency_code = $this->randomMachineName();
    $default_value = [
      'currency_code' => $currency_code,
    ];

    return [
      [TRUE, []],
      [FALSE, []],
      [NULL, []],
      [$this->randomMachineName(), []],
      [new \stdClass(), []],
      [
        array($this->randomMachineName(), $this->randomMachineName()),
        $default_value
      ],
    ];
  }

  /**
   * @covers ::process
   *
   * @depends      testGetInfo
   *
   * @dataProvider providerTestProcess
   */
  public function testProcess($default_currency_loadable) {
    $currency_code_a = $this->randomMachineName();
    $currency_code_b = $this->randomMachineName();
    $currency_code_c = $this->randomMachineName();

    $currency = $this->createMock(CurrencyInterface::class);

    $currency_options = [
      $currency_code_a => $this->randomMachineName(),
      $currency_code_b => $this->randomMachineName(),
      $currency_code_c => $this->randomMachineName(),
    ];

    $this->formHelper->expects($this->atLeastOnce())
      ->method('getCurrencyOptions')
      ->willReturn($currency_options);

    $map = [
      [$currency_code_b, $default_currency_loadable ? $currency : NULL],
      ['XXX', $default_currency_loadable ? NULL : $currency],
    ];
    $this->currencyStorage->expects($this->atLeastOnce())
      ->method('load')
      ->willReturnMap($map);

    $limit_currency_codes = [$currency_code_a, $currency_code_b];

    $element = [
        '#default_value' => [
          'amount' => mt_rand(),
          'currency_code' => $currency_code_b,
        ],
        '#required' => TRUE,
        '#limit_currency_codes' => $limit_currency_codes,
      ] + $this->sut->getInfo();

    $form_state = new FormState();
    $form = [];

    $element = $this->sut->process($element, $form_state, $form);
    $this->assertEmpty(array_diff($limit_currency_codes, array_keys($element['currency_code']['#options'])));
    $this->assertEmpty(array_diff(array_keys($element['currency_code']['#options']), $limit_currency_codes));
  }

  /**
   * Provides data to self::testProcess().
   */
  public function providerTestProcess() {
    return [
      [TRUE],
      [FALSE],
    ];
  }

}
