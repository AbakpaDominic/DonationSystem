<?php

namespace Drupal\Tests\currency\Functional\Entity\Currency;

use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Entity\CurrencyDeleteForm web test.
 *
 * @group Currency
 */
class CurrencyDeleteFormWebTest extends BrowserTestBase {

  public static $modules = array('currency');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the form.
   */
  function testForm() {
    $user = $this->drupalCreateUser(array('currency.currency.delete'));
    $this->drupalLogin($user);

    $storage = \Drupal::entityTypeManager()->getStorage('currency');

    $currency = $storage->create(array(
      'currencyCode' => 'ABC',
      'label' => 'ABC',
    ));
    $currency->save();
    $this->drupalGet('admin/config/regional/currency/' . $currency->id() . '/delete');
    $this->submitForm(array(), t('Delete'));
    $storage->resetCache();
    $this->assertFalse((bool) $storage->load($currency->id()));
  }
}
