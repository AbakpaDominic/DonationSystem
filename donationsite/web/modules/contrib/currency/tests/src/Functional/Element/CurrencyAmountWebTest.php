<?php

namespace Drupal\Tests\currency\Functional\Element;

use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Element\CurrencyAmount web test.
 *
 * @group Currency
 */
class CurrencyAmountWebTest extends BrowserTestBase {

  public static $modules = array('currency_test');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    /** @var \Drupal\currency\ConfigImporterInterface $config_importer */
    $config_importer = \Drupal::service('currency.config_importer');
    $config_importer->importCurrency('EUR');
  }

  /**
   * Test validation.
   */
  function testValidation() {
    $state = \Drupal::state();
    $path = 'currency_test-form-element-currency-amount/50.00/100';

    // Test valid values.
    $values =  array(
      'container[amount][amount]' => '50,95',
      'container[amount][currency_code]' => 'EUR',
    );
    $this->drupalGet($path);
    $this->submitForm($values, t('Submit'));
    $amount = $state->get('currency_test_currency_amount_element');
    $this->assertEquals(50.95, $amount['amount']);
    $this->assertEquals('EUR', $amount['currency_code']);

    // Test valid values with a predefined currency.
    $this->drupalGet($path . '/NLG');
    $this->assertSession()->fieldNotExists('container[amount][currency_code]');
    $values =  array(
      'container[amount][amount]' => '50,95',
    );
    $this->submitForm($values, t('Submit'));
    $amount = $state->get('currency_test_currency_amount_element');
    $this->assertEquals(50.95, $amount['amount']);
    $this->assertEquals('NLG', $amount['currency_code']);

    // Test invalid values.
    $invalid_amounts = array(
      // Illegal characters.
      $this->randomMachineName(2),
      // Multiple decimal marks.
      '49,.95',
      // A value that is below the minimum.
      '49.95',
      // A value that exceeds the maximum.
      '999'
    );
    foreach ($invalid_amounts as $amount) {
      $values =  array(
        'container[amount][amount]' => $amount,
      );
      $this->drupalGet($path);
      $this->submitForm($values, t('Submit'));
      $this->assertSession()->elementExists('css', 'input.error[name="container[amount][amount]"]');
      $this->assertSession()->elementNotExists('css', 'input.error[name!="container[amount][amount]"]');
    }
  }
}
