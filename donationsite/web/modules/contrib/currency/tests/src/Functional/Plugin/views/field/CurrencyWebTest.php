<?php

namespace Drupal\Tests\currency\Functional\Plugin\views\field;

use Drupal\Tests\BrowserTestBase;
use Drupal\views\Entity\View;

/**
 * \Drupal\currency\Plugin\views\field\Currency web test.
 *
 * @group Currency
 */
class CurrencyWebTest extends BrowserTestBase {

  public static $modules = array('currency_test', 'views');

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
   * Tests the handler.
   */
  public function testHandler() {
    /** @var \Drupal\views\Entity\View $view */
    $view = View::load('currency_test');
    $view->getExecutable()->execute('default');
    $this->assertEquals('€', $view->get('executable')->field['currency_sign']->advancedRender($view->get('executable')->result[0]));
    $this->assertEquals('100', $view->get('executable')->field['currency_subunits']->advancedRender($view->get('executable')->result[0]));
  }
}
