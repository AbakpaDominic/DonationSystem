<?php

namespace Drupal\Tests\currency\Functional\Controller;

use Drupal\Tests\BrowserTestBase;

/**
 * \Drupal\currency\Controller\Currency web test.
 *
 * @group Currency
 */
class CurrencyWebTest extends BrowserTestBase {

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
    $config_importer->importCurrency('AFN');
    $config_importer->importCurrency('EUR');
  }

  /**
   * Tests the user interface.
   */
  function testUserInterface() {
    $currency_overview_path = 'admin/config/regional/currency';
    $regional_path = 'admin/config/regional';

    // Test the appearance of the link on the "Regional and language" page.
    $account = $this->drupalCreateUser(array('access administration pages'));
    $this->drupalLogin($account);
    $this->drupalGet($regional_path);
    $this->assertSession()->statusCodeEquals('200');
    $this->assertSession()->linkByHrefNotExists($currency_overview_path);
    $this->drupalGet($currency_overview_path);
    $this->assertSession()->statusCodeEquals('403');
    $account = $this->drupalCreateUser(array('currency.currency.view', 'access administration pages'));
    $this->drupalLogin($account);
    $this->drupalGet($regional_path);
    $this->assertSession()->statusCodeEquals('200');
    $this->assertSession()->linkByHrefExists($currency_overview_path);
    $this->drupalLogout();

    // Test the currency locale overview.
    $this->drupalGet($currency_overview_path);
    $this->assertSession()->statusCodeEquals('403');
    $account = $this->drupalCreateUser(array('currency.currency.view'));
    $this->drupalLogin($account);
    $this->drupalGet($currency_overview_path);
    $this->assertSession()->statusCodeEquals('200');
    $this->assertSession()->pageTextContains('euro');
    $this->assertSession()->linkNotExists(t('Edit'));
    $this->assertSession()->linkNotExists(t('Delete'));
    $account = $this->drupalCreateUser(array('currency.currency.view', 'currency.currency.update', 'currency.currency.delete'));
    $this->drupalLogin($account);
    $this->drupalGet($currency_overview_path);
    $this->assertSession()->linkByHrefExists('admin/config/regional/currency/EUR');
    $this->assertSession()->linkByHrefExists('admin/config/regional/currency/EUR/delete');
    // Make sure that there is an edit link, but no delete link for the default
    // currency.
    $this->assertSession()->linkByHrefExists('admin/config/regional/currency/XXX');
    $this->assertSession()->linkByHrefNotExists('admin/config/regional/currency/XXX/delete');

    // Test that the "Edit" operation link works.
    $this->clickLink(t('Edit'));
    $this->assertSession()->addressEquals('admin/config/regional/currency/AFN');
    $this->assertSession()->statusCodeEquals('200');
    // Test that the "Delete" form action button works.
    $this->clickLink(t('Delete'));
    $this->assertSession()->addressEquals('admin/config/regional/currency/AFN/delete');
    $this->assertSession()->statusCodeEquals('200');

    // Test that the "Delete" operation link works.
    $this->drupalGet($currency_overview_path);
    $this->clickLink(t('Delete'));
    $this->assertSession()->addressEquals('admin/config/regional/currency/AFN/delete');
    $this->assertSession()->statusCodeEquals('200');
  }
}
