<?php

namespace Drupal\Tests\currency\Functional\Entity\CurrencyLocale;

use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleDeleteForm web test.
 *
 * @group Currency
 */
class CurrencyLocaleDeleteFormWebTest extends BrowserTestBase {

  public static $modules = array('currency');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the form.
   */
  function testForm() {
    $user = $this->drupalCreateUser(array('currency.currency_locale.delete'));
    $this->drupalLogin($user);

    $storage = \Drupal::entityTypeManager()->getStorage('currency_locale');
    $currency_locale = $storage->create(array(
      'locale' => 'zz_ZZ',
    ));
    $currency_locale->save();
    $this->drupalGet('admin/config/regional/currency-formatting/locale/' . $currency_locale->id() . '/delete');
    $this->submitForm(array(), t('Delete'));
    $storage->resetCache();
    $this->assertFalse((bool) $storage->load($currency_locale->id()));
  }
}
