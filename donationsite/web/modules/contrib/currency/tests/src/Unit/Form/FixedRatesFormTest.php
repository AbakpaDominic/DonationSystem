<?php

namespace Drupal\Tests\currency\Unit\Form {

  use Commercie\CurrencyExchange\ExchangeRateInterface;
  use Drupal\Core\Config\ConfigFactoryInterface;
  use Drupal\Core\Entity\EntityTypeManagerInterface;
  use Drupal\Core\Entity\EntityStorageInterface;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Messenger\MessengerInterface;
  use Drupal\currency\Form\FixedRatesForm;
  use Drupal\currency\Entity\CurrencyInterface;
  use Drupal\currency\FormHelperInterface;
  use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderInterface;
  use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface;
  use Drupal\currency\Plugin\Currency\ExchangeRateProvider\FixedRates;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
 * @coversDefaultClass \Drupal\currency\Form\FixedRatesForm
 *
 * @group Currency
 */
class FixedRatesFormTest extends UnitTestCase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The currency exchange rate provider manager.
   *
   * @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyExchangeRateProviderManager;

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currencyStorage;

  /**
   * The form helper
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
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Form\FixedRatesForm
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);

    $this->currencyExchangeRateProviderManager = $this->createMock(ExchangeRateProviderManagerInterface::class);

    $this->currencyStorage = $this->createMock(EntityStorageInterface::class);

    $this->formHelper = $this->createMock(FormHelperInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->messenger = $this->createMock(MessengerInterface::class);

    $this->sut = new FixedRatesForm($this->configFactory, $this->stringTranslation, $this->currencyStorage, $this->currencyExchangeRateProviderManager, $this->formHelper);
    $this->sut->setMessenger($this->messenger);
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
      array('config.factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configFactory),
      array('currency.form_helper', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->formHelper),
      array('entity_type.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_type_manager),
      array('plugin.manager.currency.exchange_rate_provider', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currencyExchangeRateProviderManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = FixedRatesForm::create($container);
    $this->assertInstanceOf(FixedRatesForm::class, $sut);
  }

  /**
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertSame('currency_exchange_rate_provider_fixed_rates', $this->sut->getFormId());
  }

  /**
   * @covers ::buildForm
   *
   * @dataProvider providerTestBuildForm
   */
  public function testBuildForm($rate_rate) {
    $currency_code_from = $this->randomMachineName();
    $currency_code_to = $this->randomMachineName();

    $rate = NULL;
    if (!is_null($rate_rate)) {
      $rate = $this->createMock(ExchangeRateInterface::class);
      $rate->expects($this->once())
        ->method('getRate')
        ->willReturn($rate_rate);
    }

    $plugin = $this->createMock(ExchangeRateProviderInterface::class);
    $plugin->expects($this->once())
      ->method('load')
      ->with($currency_code_from, $currency_code_to)
      ->willReturn($rate);

    $this->currencyExchangeRateProviderManager->expects($this->once())
      ->method('createInstance')
      ->with('currency_fixed_rates')
      ->willReturn($plugin);

    $currency_options = array(
      'XXX' => $this->randomMachineName(),
      $this->randomMachineName() => $this->randomMachineName(),
    );

    $this->formHelper->expects($this->once())
      ->method('getCurrencyOptions')
      ->willReturn($currency_options);

    unset($currency_options['XXX']);

    $form = array();
    $form_state = $this->createMock(FormStateInterface::class);
    $build = $this->sut->buildForm($form, $form_state, $currency_code_from, $currency_code_to);

    $expected_build['currency_code_from'] = array(
      '#default_value' => $currency_code_from,
      '#disabled' => !is_null($rate_rate),
      '#empty_value' => '',
      '#options' => $currency_options,
      '#required' => TRUE,
      '#type' => 'select',
    );
    unset($build['currency_code_from']['#title']);
    $expected_build['currency_code_to'] = array(
      '#default_value' => $currency_code_to,
      '#disabled' => !is_null($rate_rate),
      '#empty_value' => '',
      '#options' => $currency_options,
      '#required' => TRUE,
      '#type' => 'select',
    );
    unset($build['currency_code_to']['#title']);
    $expected_build['rate'] = array(
      '#limit_currency_codes' => array($currency_code_to),
      '#default_value' => array(
        'amount' => $rate_rate,
        'currency_code' => $currency_code_to,
      ),
      '#required' => TRUE,
      '#type' => 'currency_amount',
    );
    unset($build['rate']['#title']);
    $expected_build['actions'] = array(
      '#type' => 'actions',
    );
    $expected_build['actions']['save'] = array(
      '#button_type' => 'primary',
      '#name' => 'save',
      '#type' => 'submit',
    );
    unset($build['actions']['save']['#value']);
    if (!is_null($rate_rate)) {
      $expected_build['actions']['delete'] = array(
        '#button_type' => 'danger',
        '#limit_validation_errors' => array(array('currency_code_from'), array('currency_code_to')),
        '#name' => 'delete',
        '#type' => 'submit',
      );
      unset($build['actions']['delete']['#value']);
    }

    $this->assertSame($expected_build, $build);
  }

  /**
   * Provides data to self::testBuildForm().
   */
  public function providerTestBuildForm() {
    return array(
      array(NULL),
      array(mt_rand()),
    );
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitFormWithSave() {
    $currency_code_from = $this->randomMachineName();
    $currency_code_to = $this->randomMachineName();
    $rate = mt_rand();

    $values = [
      'currency_code_from' => $currency_code_from,
      'currency_code_to' => $currency_code_to,
      'rate' => [
        'amount' => $rate,
      ],
    ];

    $form = [
      'actions' => [
        'save' => [
          '#name' => 'save',
          '#foo' => $this->randomMachineName(),
        ],
        'delete' => [
          '#name' => 'delete',
          '#foo' => $this->randomMachineName(),
        ],
      ],
    ];

    $form_state = $this->createMock(FormStateInterface::class);
    $form_state->expects($this->atLeastOnce())
      ->method('getTriggeringElement')
      ->willReturn($form['actions']['save']);
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn($values);
    $form_state->expects($this->atLeastOnce())
      ->method('setRedirect')
      ->with('currency.exchange_rate_provider.fixed_rates.overview');

    $exchange_rate_provider = $this->getMockBuilder(FixedRates::class)
      ->disableOriginalConstructor()
      ->getMock();
    $exchange_rate_provider->expects($this->once())
      ->method('save')
      ->with($currency_code_from, $currency_code_to, $rate);

    $this->currencyExchangeRateProviderManager->expects($this->once())
      ->method('createInstance')
      ->with('currency_fixed_rates')
      ->willReturn($exchange_rate_provider);

    $currency_from = $this->createMock(CurrencyInterface::class);
    $currency_to = $this->createMock(CurrencyInterface::class);

    $map = [
      [$currency_code_from, $currency_from],
      [$currency_code_to, $currency_to],
    ];
    $this->currencyStorage->expects($this->atLeastOnce())
      ->method('load')
      ->willReturnMap($map);

    $this->sut->submitForm($form, $form_state);
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitFormWitDelete() {
    $currency_code_from = $this->randomMachineName();
    $currency_code_to = $this->randomMachineName();

    $values = [
      'currency_code_from' => $currency_code_from,
      'currency_code_to' => $currency_code_to,
    ];

    $form = [
      'actions' => [
        'save' => [
          '#name' => 'save',
          '#foo' => $this->randomMachineName(),
        ],
        'delete' => [
          '#name' => 'delete',
          '#foo' => $this->randomMachineName(),
        ],
      ],
    ];

    $form_state = $this->createMock(FormStateInterface::class);
    $form_state->expects($this->atLeastOnce())
      ->method('getTriggeringElement')
      ->willReturn($form['actions']['delete']);
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn($values);
    $form_state->expects($this->atLeastOnce())
      ->method('setRedirect')
      ->with('currency.exchange_rate_provider.fixed_rates.overview');

    $exchange_rate_provider = $this->getMockBuilder(FixedRates::class)
      ->disableOriginalConstructor()
      ->getMock();
    $exchange_rate_provider->expects($this->once())
      ->method('delete')
      ->with($currency_code_from, $currency_code_to);

    $this->currencyExchangeRateProviderManager->expects($this->once())
      ->method('createInstance')
      ->with('currency_fixed_rates')
      ->willReturn($exchange_rate_provider);

    $currency_from = $this->createMock(CurrencyInterface::class);
    $currency_to = $this->createMock(CurrencyInterface::class);

    $map = [
      [$currency_code_from, $currency_from],
      [$currency_code_to, $currency_to],
    ];
    $this->currencyStorage->expects($this->atLeastOnce())
      ->method('load')
      ->willReturnMap($map);

    $this->sut->submitForm($form, $form_state);
  }

}

}
