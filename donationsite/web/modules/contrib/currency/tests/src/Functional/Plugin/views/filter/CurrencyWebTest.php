<?php

namespace Drupal\Tests\currency\Functional\Plugin\views\filter;

use Drupal\Tests\BrowserTestBase;
use Drupal\views\Entity\View;

/**
 * \Drupal\currency\Plugin\views\filter\Currency web test.
 *
 * @group Currency
 */
class CurrencyWebTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('currency_test', 'views_ui');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the handler.
   */
  public function testHandler() {
    $view_id = 'currency_test';
    $view = View::load($view_id);
    $view->getExecutable()->execute('default');
    // There are four rows, and the filter excludes NLG.
    $this->assertCount(3, $view->get('executable')->result);

    $account = $this->drupalCreateUser(array('administer views'));
    $this->drupalLogin($account);
    $this->drupalGet('admin/structure/views/nojs/handler/' . $view_id . '/default/filter/currency');
    /** @var \Drupal\currency\FormHelperInterface $form_helper */
    $form_helper = \Drupal::service('currency.form_helper');
    foreach ($form_helper->getCurrencyOptions() as $option) {
      $this->assertSession()->pageTextContains($option);
    }
  }
}
