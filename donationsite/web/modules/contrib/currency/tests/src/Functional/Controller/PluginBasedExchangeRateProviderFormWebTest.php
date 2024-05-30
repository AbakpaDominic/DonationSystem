<?php

namespace Drupal\Tests\currency\Functional\Controller;

use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Form\PluginBasedExchangeRateProviderForm web test.
 *
 * @group Currency
 */
class PluginBasedExchangeRateProviderFormWebTest extends BrowserTestBase {

  public static $modules = array('currency');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test CurrencyExchanger's UI.
   */
  function testCurrencyExchangerUI() {
    $exchange_delegator = \Drupal::service('currency.exchange_rate_provider');

    $user = $this->drupalCreateUser(array('currency.exchange_rate_provider.administer'));
    $this->drupalLogin($user);

    // Test the default configuration.
    $this->assertEquals(array(
      'currency_fixed_rates' => TRUE,
      'currency_historical_rates' => TRUE,
    ), $exchange_delegator->loadConfiguration());
    // Test overridden configuration.
    $path = 'admin/config/regional/currency-exchange';
    $values = array(
      'exchange_rate_providers[currency_fixed_rates][enabled]' => FALSE,
    );
    $this->drupalGet($path);
    $this->submitForm($values, t('Save'));
    $this->assertEquals(array(
      'currency_fixed_rates' => FALSE,
      'currency_historical_rates' => TRUE,
    ), $exchange_delegator->loadConfiguration());
  }
}
