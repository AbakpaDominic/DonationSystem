<?php

namespace Drupal\Tests\currency_intl\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Module installation and uninstallation.
 *
 * @group Currency Intl
 */
class ModuleInstallUninstallWebTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('currency_intl');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test uninstall.
   */
  function testUninstallation() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = \Drupal::service('module_installer');
    $module_handler = \Drupal::moduleHandler();
    $this->assertTrue($module_handler->moduleExists('currency_intl'));
    $module_installer->uninstall(array('currency_intl'));
    $this->assertFalse($module_handler->moduleExists('currency_intl'));
  }
}
