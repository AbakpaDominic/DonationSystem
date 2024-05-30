<?php

namespace Drupal\Tests\currency\Functional\Element;

use Drupal\currency\Element\CurrencySign;
use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Element\CurrencySign web test.
 *
 * @group Currency
 */
class CurrencySignWebTest extends BrowserTestBase {

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
    $path = 'currency_test-form-element-currency-sign';

    // Test an empty sign.
    $values =  array(
      'container[sign][sign]' => '',
    );
    $this->drupalGet($path);
    $this->submitForm($values, t('Submit'));
    $sign = $state->get('currency_test_currency_sign_element');
    $this->assertEquals('', $sign);

    // Test a suggested sign.
    $values =  array(
      'container[sign][sign]' => '€',
    );
    $this->drupalGet($path . '/EUR');
    $this->submitForm($values, t('Submit'));
    $sign = $state->get('currency_test_currency_sign_element');
    $this->assertEquals('€', $sign);

    // Test a custom sign.
    $values =  array(
      'container[sign][sign]' => CurrencySign::CUSTOM_VALUE,
      'container[sign][sign_custom]' => 'foobar',
    );
    $this->drupalGet($path);
    $this->submitForm($values, t('Submit'));
    $sign = $state->get('currency_test_currency_sign_element');
    $this->assertEquals('foobar', $sign);
    $this->drupalGet($path . '/EUR/foobar');
    $this->assertSession()->responseContains('<option value="' . CurrencySign::CUSTOM_VALUE . '" selected="selected">');
    // Check if the sign element is set to a custom value.
    $this->assertEquals('selected', $this->assertSession()->optionExists('container[sign][sign]', CurrencySign::CUSTOM_VALUE)->getAttribute('selected'));
    // Check if the custom sign input element has the custom sign as its value.
    $this->assertSession()->fieldValueEquals('container[sign][sign_custom]', 'foobar');

    // Test a non-existing currency.
    $values =  array(
      'container[sign][sign]' => '',
    );
    $this->drupalGet($path);
    $this->submitForm($values, t('Submit'));
    $sign = $state->get('currency_test_currency_sign_element');
    $this->assertEquals('', $sign);
  }
}
