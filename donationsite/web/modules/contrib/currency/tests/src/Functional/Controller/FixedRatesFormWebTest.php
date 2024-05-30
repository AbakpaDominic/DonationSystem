<?php

namespace Drupal\Tests\currency\Functional\Controller;

use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Form\FixedRatesForm web test.
 *
 * @group Currency
 */
class FixedRatesFormWebTest extends BrowserTestBase {

  public static $modules = array('currency');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the form.
   */
  function testForm() {
    /** @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderInterface $plugin */
    $plugin = \Drupal::service('plugin.manager.currency.exchange_rate_provider')->createInstance('currency_fixed_rates');

    $user = $this->drupalCreateUser(array('currency.exchange_rate_provider.fixed_rates.administer'));
    $this->drupalLogin($user);
    $path = 'admin/config/regional/currency-exchange/fixed';

    // Test the overview.
    $this->drupalGet($path);
    $this->assertSession()->pageTextContains(t('Add an exchange rate'));

    // Set up the currencies.
    /** @var \Drupal\currency\ConfigImporterInterface $config_importer */
    $config_importer = \Drupal::service('currency.config_importer');
    $config_importer->importCurrency('EUR');
    $config_importer->importCurrency('UAH');
    $currency_code_from = 'EUR';
    $currency_code_to = 'UAH';

    // Test adding a exchange rate.
    $rate = '3';
    $values = array(
      'currency_code_from' => $currency_code_from,
      'currency_code_to' => $currency_code_to,
      'rate[amount]' => $rate,
    );
    $this->drupalGet($path . '/add');
    $this->submitForm($values, t('Save'));
    $exchange_rate = $plugin->load($currency_code_from, $currency_code_to);
    $this->assertSame($exchange_rate->getRate(), $rate);
    $this->assertSame($exchange_rate->getSourceCurrencyCode(), $currency_code_from);
    $this->assertSame($exchange_rate->getDestinationCurrencyCode(), $currency_code_to);

    // Test editing a exchange rate.
    $rate = '6';
    $values = array(
      'rate[amount]' => $rate,
    );
    $this->drupalGet($path . '/' . $currency_code_from . '/' . $currency_code_to);
    $this->submitForm($values, t('Save'));
    $exchange_rate = $plugin->load($currency_code_from, $currency_code_to);
    $this->assertSame($exchange_rate->getRate(), $rate);

    // Test deleting a exchange rate.
    $this->drupalGet($path . '/' . $currency_code_from . '/' . $currency_code_to);
    $this->submitForm($values, t('Delete'));
    $this->assertNull($plugin->load($currency_code_from, $currency_code_to));
  }
}
