<?php

namespace Drupal\Tests\currency\Unit\Entity\CurrencyLocale {

  use Drupal\Core\Entity\EntityTypeManagerInterface;
  use Drupal\Core\Entity\EntityStorageInterface;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Form\FormValidatorInterface;
  use Drupal\Core\Language\LanguageInterface;
  use Drupal\Core\Locale\CountryManagerInterface;
  use Drupal\Core\Messenger\MessengerInterface;
  use Drupal\Core\Utility\LinkGeneratorInterface;
  use Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleForm;
  use Drupal\currency\Entity\CurrencyLocaleInterface;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerBuilder;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleForm
   *
   * @group Currency
   */
  class CurrencyLocaleFormTest extends UnitTestCase {

    /**
     * The country manager.
     *
     * @var \Drupal\Core\Locale\CountryManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $countryManager;

    /**
     * The currency locale.
     *
     * @var \Drupal\currency\Entity\CurrencyLocaleInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $currencyLocale;

    /**
     * The currency locale storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $currencyLocaleStorage;

    /**
     * The link generator.
     *
     * @var \Drupal\Core\Utility\LinkGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $linkGenerator;

    /**
     * The string translator.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stringTranslation;

    /**
     * The class under test.
     *
     * @var \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleForm
     */
    protected $sut;

    /**
     * The form validator.
     *
     * @var \Drupal\Core\Form\FormValidatorInterface
     */
    protected $formValidator;

    /**
     * The messenger.
     *
     * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messenger;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void {
      $this->countryManager = $this->createMock(CountryManagerInterface::class);

      $this->currencyLocale = $this->createMock(CurrencyLocaleInterface::class);

      $this->currencyLocaleStorage = $this->createMock(EntityStorageInterface::class);

      $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->formValidator = $this->createMock(FormValidatorInterface::class);

      $this->messenger = $this->createMock(MessengerInterface::class);

      $container = new ContainerBuilder();
      $container->set('form_validator', $this->formValidator);
      \Drupal::setContainer($container);

      $this->sut = new CurrencyLocaleForm($this->stringTranslation, $this->linkGenerator, $this->currencyLocaleStorage, $this->countryManager);
      $this->sut->setEntity($this->currencyLocale);
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
        ->with('currency_locale')
        ->willReturn($this->currencyLocaleStorage);

      $container = $this->createMock(ContainerInterface::class);

      $map = array(
        array('country_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->countryManager),
        array('entity_type.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_type_manager),
        array('link_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->linkGenerator),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $form = CurrencyLocaleForm::create($container);
      $this->assertInstanceOf(CurrencyLocaleForm::class, $form);
    }

    /**
     * @covers ::copyFormValuesToEntity
     */
    public function testCopyFormValuesToEntity() {
      $language_code = $this->randomMachineName();
      $country_code = $this->randomMachineName();
      $pattern = $this->randomMachineName();
      $decimal_separator = $this->randomMachineName();
      $grouping_separator = $this->randomMachineName();

      $this->currencyLocale->expects($this->once())
        ->method('setLocale')
        ->with($language_code, $country_code);
      $this->currencyLocale->expects($this->once())
        ->method('setPattern')
        ->with($pattern);
      $this->currencyLocale->expects($this->once())
        ->method('setDecimalSeparator')
        ->with($decimal_separator);
      $this->currencyLocale->expects($this->once())
        ->method('setGroupingSeparator')
        ->with($grouping_separator);

      $form = array();
      $form_state = $this->createMock(FormStateInterface::class);
      $form_state->expects($this->atLeastOnce())
        ->method('getValues')
        ->willReturn(array(
          'language_code' => $language_code,
          'country_code' => $country_code,
          'pattern' => $pattern,
          'decimal_separator' => $decimal_separator,
          'grouping_separator' => $grouping_separator,
        ));

      $method = new \ReflectionMethod($this->sut, 'copyFormValuesToEntity');
      $method->setAccessible(TRUE);

      $method->invokeArgs($this->sut, array($this->currencyLocale, $form, $form_state));
    }

    /**
     * @covers ::form
     */
    public function testForm() {
      $language_code = $this->randomMachineName();
      $country_code = $this->randomMachineName();
      $pattern = $this->randomMachineName();
      $decimal_separator = $this->randomMachineName();
      $grouping_separator = $this->randomMachineName();

      $this->currencyLocale->expects($this->once())
        ->method('getLanguageCode')
        ->willReturn($language_code);
      $this->currencyLocale->expects($this->once())
        ->method('getCountryCode')
        ->willReturn($country_code);
      $this->currencyLocale->expects($this->once())
        ->method('getPattern')
        ->willReturn($pattern);
      $this->currencyLocale->expects($this->once())
        ->method('getDecimalSeparator')
        ->willReturn($decimal_separator);
      $this->currencyLocale->expects($this->once())
        ->method('getGroupingSeparator')
        ->willReturn($grouping_separator);

      $language = $this->createMock(LanguageInterface::class);

      $this->currencyLocale->expects($this->any())
        ->method('language')
        ->willReturn($language);

      $country_list = array(
        $this->randomMachineName() => $this->randomMachineName(),
      );
      $this->countryManager->expects($this->atLeastOnce())
        ->method('getList')
        ->willReturn($country_list);

      $form = array();
      $form_state = $this->createMock(FormStateInterface::class);

      $build = $this->sut->form($form, $form_state);
      unset($build['langcode']);
      unset($build['#process']);

      $expected['language_code'] = [
        '#default_value' => $language_code,
        '#empty_value' => '',
        '#required' => TRUE,
        '#type' => 'select',
      ];
      unset($build['language_code']['#title']);
      unset($build['language_code']['#options']);
      $expected['country_code'] = [
        '#default_value' => $country_code,
        '#empty_value' => '',
        '#options' => $country_list,
        '#required' => TRUE,
        '#type' => 'select',
      ];
      unset($build['country_code']['#title']);
      $expected['formatting'] = [
        '#open' => TRUE,
        '#type' => 'details',
      ];
      unset($build['formatting']['#title']);
      $expected['formatting']['decimal_separator'] = [
        '#default_value' => $decimal_separator,
        '#maxlength' => 255,
        '#required' => TRUE,
        '#size' => 3,
        '#type' => 'textfield',
      ];
      unset($build['formatting']['decimal_separator']['#title']);
      $expected['formatting']['grouping_separator'] = [
        '#default_value' => $grouping_separator,
        '#maxlength' => 255,
        '#size' => 3,
        '#type' => 'textfield',
      ];
      unset($build['formatting']['grouping_separator']['#title']);
      $expected['formatting']['pattern'] = [
        '#default_value' => $pattern,
        '#maxlength' => 255,
        '#type' => 'textfield',
      ];
      unset($build['formatting']['pattern']['#title']);
      unset($build['formatting']['pattern']['#description']);
      $expected['#after_build'] = ['::afterBuild'];

      $this->assertSame($expected, $build);
    }

    /**
     * @covers ::save
     */
    public function testSave() {
      $this->currencyLocale->expects($this->once())
        ->method('save');

      $form = array();
      $form_state = $this->createMock(FormStateInterface::class);
      $form_state->expects($this->once())
        ->method('setRedirect')
        ->with('entity.currency_locale.collection');

      $this->sut->save($form, $form_state);
    }

    /**
     * @covers ::validateForm
     * @dataProvider providerTestValidate
     */
    public function testValidateForm($input_value_language_code, $input_value_country_code, $locale, $currency_locale_is_new, $locale_is_used) {
      $form = array(
        'locale' => array(
          '#foo' => $this->randomMachineName(),
        ),
      );
      $form_state = $this->createMock(FormStateInterface::class);
      $form_state->expects($this->any())
        ->method('getValues')
        ->willReturn(array(
          'country_code' => $input_value_country_code,
          'language_code' => $input_value_language_code,
        ));

      $this->currencyLocale->expects($this->atLeastOnce())
        ->method('isNew')
        ->willReturn($currency_locale_is_new);

      if ($currency_locale_is_new) {
        if ($locale_is_used) {
          $loaded_currency_locale = $this->createMock(CurrencyLocaleInterface::class);

          $this->currencyLocaleStorage->expects($this->once())
            ->method('load')
            ->with($locale)
            ->willReturn($loaded_currency_locale);

          $form_state->expects($this->once())
            ->method('setError')
            ->with($form['locale'], 'A pattern for this locale already exists.');
        }
        else {
          $this->currencyLocaleStorage->expects($this->once())
            ->method('load')
            ->with($locale)
            ->willReturn(FALSE);

          $form_state->expects($this->never())
            ->method('setError');
        }
      }
      else {
        $this->currencyLocaleStorage->expects($this->never())
          ->method('load');

        $form_state->expects($this->never())
          ->method('setError');
      }

      $this->sut->validateForm($form, $form_state);
    }

    /**
     * Provides data to self::testValidate()
     */
    public function providerTestValidate() {
      return array(
        array('Nl', 'nL', 'nl_NL', FALSE, FALSE),
        array('UK', 'ua', 'uk_UA', TRUE, FALSE),
        array('nl', 'NL', 'nl_NL', TRUE, TRUE),
        array('uk', 'UA', 'uk_UA', FALSE, TRUE),
      );
    }

  }

}
