<?php

namespace Drupal\Tests\currency\Functional\Plugin\views\field;

use Drupal\Tests\BrowserTestBase;
use Drupal\views\Entity\View;

/**
 * \Drupal\currency\Plugin\views\field\Amount web test.
 *
 * @group Currency
 */
class AmountWebTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('currency_test', 'views_ui');

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
    $config_importer->importCurrency('UAH');
    $config_importer->importCurrency('USD');
  }

  /**
   * Tests the handler.
   */
  public function testHandler() {
    $view_id = 'currency_test';

    // Test view creation/editing.
    $account = $this->drupalCreateUser(array('administer views'));
    $this->drupalLogin($account);
    $this->drupalGet('admin/structure/views/nojs/handler/' . $view_id . '/default/field/amount_currency_code_definition');
    $this->submitForm(array(
      'options[currency_round]' => TRUE,
    ), t('Apply'));
    $this->drupalGet('admin/structure/views/view/' . $view_id);
    $this->submitForm(array(), t('Save'));

    // Test view display.
    /** @var \Drupal\views\Entity\View $view */
    $view = View::load($view_id);
    $view->getExecutable()->execute('default');
    $values = array(
      array(
        // The amount_currency_code_definition field is rounded.
        'amount_currency_code_definition' => 'EUR 123.46',
        'amount_currency_code_field_definition' => 'EUR 123.456',
        'amount_currency_code_field_table_definition' => 'EUR 123.456',
        'amount_currency_undefined' => 'XXX 123.456',
      ),
      array(
        // The amount_currency_code_definition field is rounded.
        'amount_currency_code_definition' => 'EUR 123.46',
        'amount_currency_code_field_definition' => 'USD 123.456',
        'amount_currency_code_field_table_definition' => 'USD 123.456',
        'amount_currency_undefined' => 'XXX 123.456',
      ),
      array(
        // The amount_currency_code_definition field is rounded.
        'amount_currency_code_definition' => 'EUR 123.46',
        'amount_currency_code_field_definition' => 'UAH 123.456',
        'amount_currency_code_field_table_definition' => 'UAH 123.456',
        'amount_currency_undefined' => 'XXX 123.456',
      ),
    );
    foreach ($values as $row => $row_values) {
      foreach ($row_values as $field => $value) {
        $this->assertEquals($value, $view->get('executable')->field[$field]->advancedRender($view->get('executable')->result[$row]));
      }
    }
  }
}
