<?php

namespace Drupal\Tests\currency\Unit\Controller {

  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Messenger\MessengerInterface;
  use Drupal\Core\Url;
  use Drupal\currency\ConfigImporterInterface;
  use Drupal\currency\Form\CurrencyLocaleImportForm;
  use Drupal\currency\Entity\CurrencyInterface;
  use Drupal\currency\Entity\CurrencyLocaleInterface;
  use Drupal\currency\FormHelperInterface;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\currency\Form\CurrencyLocaleImportForm
   *
   * @group Currency
   */
  class CurrencyLocaleImportFormTest extends UnitTestCase {

    /**
     * The config importer.
     *
     * @var \Drupal\currency\ConfigImporterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configImporter;

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
     * The messenger.
     *
     * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messenger;

    /**
     * The class under test.
     *
     * @var \Drupal\currency\Form\CurrencyLocaleImportForm
     */
    protected $sut;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void {
      $this->configImporter = $this->createMock(ConfigImporterInterface::class);

      $this->formHelper = $this->createMock(FormHelperInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->messenger = $this->createMock(MessengerInterface::class);

      $this->sut = new CurrencyLocaleImportForm($this->stringTranslation, $this->configImporter, $this->formHelper);
      $this->sut->setMessenger($this->messenger);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $container = $this->createMock(ContainerInterface::class);
      $map = [
        ['currency.config_importer', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configImporter],
        ['currency.form_helper', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->formHelper],
        ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
      ];
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $sut = CurrencyLocaleImportForm::create($container);
      $this->assertInstanceOf(CurrencyLocaleImportForm::class, $sut);
    }

    /**
     * @covers ::getFormId
     */
    public function testGetFormId() {
      $this->assertIsString($this->sut->getFormId());
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildFormWithoutImportableCurrencies() {
      $this->configImporter->expects($this->once())
        ->method('getImportableCurrencyLocales')
        ->willReturn([]);

      $form_state = $this->createMock(FormStateInterface::class);

      $form = $this->sut->buildForm([], $form_state);

      // There should be one element and it must not be the currency selector or a
      // group of actions.
      $this->assertCount(1, $form);
      $this->assertArrayNotHasKey('actions', $form);
      $this->assertArrayNotHasKey('locale', $form);
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildFormWithImportableCurrencyLocales() {
      $currency_locale_a = $this->createMock(CurrencyInterface::class);
      $currency_locale_b = $this->createMock(CurrencyInterface::class);

      $this->configImporter->expects($this->once())
        ->method('getImportableCurrencyLocales')
        ->willReturn([$currency_locale_a, $currency_locale_b]);

      $form_state = $this->createMock(FormStateInterface::class);

      $form = $this->sut->buildForm([], $form_state);

      // There should a currency selector and a group of actions.
      $this->assertArrayHasKey('locale', $form);
      $this->assertArrayHasKey('actions', $form);
      $this->assertArrayHasKey('import', $form['actions']);
      $this->assertArrayHasKey('import_edit', $form['actions']);
    }

    /**
     * @covers ::submitForm
     */
    public function testSubmitFormImport() {
      $locale = $this->randomMachineName();

      $currency_locale = $this->createMock(CurrencyLocaleInterface::class);

      $this->configImporter->expects($this->once())
        ->method('importCurrencyLocale')
        ->with($locale)
        ->willReturn($currency_locale);

      $form = [
        'actions' => [
          'import' => [
            '#name' => 'import',
          ],
          'import_edit' => [
            '#name' => 'import_edit',
          ],
        ],
      ];
      $form_state = $this->createMock(FormStateInterface::class);
      $form_state->expects($this->atLeastOnce())
        ->method('getValues')
        ->willReturn([
          'locale' => $locale,
        ]);
      $form_state->expects($this->atLeastOnce())
        ->method('getTriggeringElement')
        ->willReturn($form['actions']['import']);
      $form_state->expects($this->atLeastOnce())
        ->method('setRedirectUrl');

      $this->sut->submitForm($form, $form_state);
    }

    /**
     * @covers ::submitForm
     */
    public function testSubmitFormImportEdit() {
      $locale = $this->randomMachineName();

      $url = new Url($this->randomMachineName());

      $currency_locale = $this->createMock(CurrencyLocaleInterface::class);
      $currency_locale->expects($this->atLeastOnce())
        ->method('toUrl')
        ->with('edit-form')
        ->willReturn($url);

      $this->configImporter->expects($this->once())
        ->method('importCurrencyLocale')
        ->with($locale)
        ->willReturn($currency_locale);

      $form = [
        'actions' => [
          'import' => [
            '#name' => 'import',
          ],
          'import_edit' => [
            '#name' => 'import_edit',
          ],
        ],
      ];
      $form_state = $this->createMock(FormStateInterface::class);
      $form_state->expects($this->atLeastOnce())
        ->method('getValues')
        ->willReturn([
          'locale' => $locale,
        ]);
      $form_state->expects($this->atLeastOnce())
        ->method('getTriggeringElement')
        ->willReturn($form['actions']['import_edit']);
      $form_state->expects($this->atLeastOnce())
        ->method('setRedirectUrl');

      $this->sut->submitForm($form, $form_state);
    }

  }

}
