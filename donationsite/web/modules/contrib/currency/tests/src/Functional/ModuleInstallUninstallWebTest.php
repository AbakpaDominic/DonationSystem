<?php

namespace Drupal\Tests\currency\Functional;

use Drupal\currency\Entity\Currency;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\currency\Entity\CurrencyLocale;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Module installation and uninstallation.
 *
 * @group Currency
 */
class ModuleInstallUninstallWebTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('currency');

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests installation and uninstallation.
   */
  function testInstallationAndUninstallation() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = \Drupal::service('module_installer');
    $module_handler = \Drupal::moduleHandler();;

    $this->assertTrue(Currency::load('XXX') instanceof CurrencyInterface);
    $this->assertTrue(CurrencyLocale::load('en_US') instanceof CurrencyLocaleInterface);

    $module_installer->uninstall(array('currency'));
    $this->assertFalse($module_handler->moduleExists('currency'));
  }

}
