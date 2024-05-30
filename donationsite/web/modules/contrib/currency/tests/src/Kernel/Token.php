<?php

namespace Drupal\Tests\currency\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Token integration.
 *
 * @group Currency
 */
class Token extends KernelTestBase {

  protected static $modules = array('system', 'currency');

  /**
   * Tests token integration.
   */
  function testTokenIntegration() {
    $this->installConfig(['currency']);
    $token_service = \Drupal::token();

    $tokens = array(
      '[currency:code]' => 'XXX',
      '[currency:number]' => '999',
      '[currency:subunits]' => '0',
    );
    $data = array(
      'currency' => 'XXX',
    );
    foreach ($tokens as $token => $replacement) {
      $this->assertEquals($token_service->replace($token, $data), $replacement);
    }
  }
}
