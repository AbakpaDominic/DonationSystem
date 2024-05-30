<?php

namespace Drupal\Tests\currency\Functional\Entity\CurrencyLocale;

use Drupal\currency\Entity\CurrencyLocale;
use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleForm web test.
 *
 * @group Currency
 */
class CurrencyLocaleFormWebTest extends BrowserTestBase {

  public static $modules = array('currency');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test Currency's UI.
   */
  function testUI() {
    $user = $this->drupalCreateUser(array('currency.currency_locale.view', 'currency.currency_locale.create', 'currency.currency_locale.update', 'currency.currency_locale.delete'));
    $this->drupalLogin($user);
    $path = 'admin/config/regional/currency-formatting/locale/add';

    // Test valid values.
    $valid_values = array(
      'language_code' => 'nl',
      'country_code' => 'UA',
      'pattern' => 'foo',
      'decimal_separator' => '1',
      'grouping_separator' => 'foobar',
    );
    $this->drupalGet($path);
    $this->submitForm($valid_values, t('Save'));
    $currency = CurrencyLocale::load('nl_UA');
    $this->assertInstanceOf(CurrencyLocale::class, $currency);

    // Edit and save an existing currency.
    $path = 'admin/config/regional/currency-formatting/locale/nl_UA';
    $this->drupalGet($path);
    $this->submitForm(array(), t('Save'));
    $this->assertSession()->addressEquals('admin/config/regional/currency-formatting/locale');
  }
}
