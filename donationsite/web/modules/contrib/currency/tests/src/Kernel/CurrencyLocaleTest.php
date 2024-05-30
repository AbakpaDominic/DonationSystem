<?php

namespace Drupal\Tests\currency\Kernel;

use Drupal\currency\Entity\CurrencyLocale;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\currency\Entity\CurrencyLocale
 *
 * @group Currency
 */
class CurrencyLocaleTest extends KernelTestBase {

  /**
   * @var array
   */
  protected static $modules = array('currency');

  /**
   * @covers ::toArray
   */
  public function testToArray() {
    $language_code = strtolower($this->randomMachineName());

    $country_code = strtoupper($this->randomMachineName());

    $entity = CurrencyLocale::create();

    $expected_array = [
      'uuid' => $entity->uuid(),
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'decimalSeparator' => $this->randomMachineName(),
      'groupingSeparator' => $this->randomMachineName(),
      'locale' => $language_code . '_' . $country_code,
      'pattern' => $this->randomMachineName(),
    ];

    $entity->setLocale($language_code, $country_code);
    $entity->setDecimalSeparator($expected_array['decimalSeparator']);
    $entity->setGroupingSeparator($expected_array['groupingSeparator']);
    $entity->setPattern($expected_array['pattern']);

    $array = $entity->toArray();
    $this->assertEquals($expected_array, $array);
  }
}
