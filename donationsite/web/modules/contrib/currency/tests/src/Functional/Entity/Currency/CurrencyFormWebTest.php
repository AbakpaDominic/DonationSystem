<?php

namespace Drupal\Tests\currency\Functional\Entity\Currency;

use Drupal\currency\Element\CurrencySign;
use Drupal\currency\Entity\Currency;
use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Entity\Currency\CurrencyForm web test.
 *
 * @group Currency
 */
class CurrencyFormWebTest extends BrowserTestBase {

  public static $modules = array('currency');

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
   * Test Currency's UI.
   */
  function testUI() {
    $user = $this->drupalCreateUser(array('currency.currency.view', 'currency.currency.create', 'currency.currency.update', 'currency.currency.delete'));
    $this->drupalLogin($user);
    $path = 'admin/config/regional/currency/add';

    // Test valid values.
    $valid_values = array(
      'currency_code' => 'ABC',
      'currency_number' => '123',
      'label' => 'foo',
      'rounding_step' => '1',
      'sign[sign]' => CurrencySign::CUSTOM_VALUE,
      'sign[sign_custom]' => 'foobar',
      'subunits' => 2,
      'status' => FALSE,
    );
    $this->drupalGet($path);
    $this->submitForm($valid_values, t('Save'));
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = Currency::load('ABC');
    $this->assertFalse($currency->status());

    // Test invalid values.
    $valid_values['currency_code'] = 'XYZ';
    $valid_values['currency_number'] = '000';
    $invalid_values = array(
      'currency_code' => array('ABC', 'EUR'),
      'currency_number' => array('abc', '978'),
      'rounding_step' => array('x'),
      'subunits' => array('x'),
    );
    foreach ($invalid_values as $name => $field_invalid_values) {
      foreach ($field_invalid_values as $invalid_value) {
        $values = array(
          $name => $invalid_value,
        ) + $valid_values;
        $this->drupalGet($path);
        $this->submitForm($values, t('Save'));
        // Test that the invalid element is the only element to be flagged.
        $this->assertSession()->elementExists('css', 'input.error[name="' . $name . '"]');
        $this->assertSession()->elementNotExists('css', 'input.error[name!="' . $name . '"]');
      }
    }

    // Edit and save an existing currency.
    $path = 'admin/config/regional/currency/ABC';
    $this->drupalGet($path);
    $this->submitForm(array(), t('Save'));
    $this->assertSession()->addressEquals('admin/config/regional/currency');
  }
}
