<?php

namespace Drupal\Tests\currency\Kernel;

use Commercie\Currency\Usage;
use Drupal\currency\Entity\Currency;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\currency\Entity\Currency
 *
 * @group Currency
 */
class CurrencyTest extends KernelTestBase {

  /**
   * @var array
   */
  protected static $modules = array('currency');

  /**
   * @covers ::toArray
   */
  public function testToArray() {

    $alternative_signs = [$this->randomMachineName(), $this->randomMachineName(), $this->randomMachineName()];
    $currency_code = $this->randomMachineName();
    $currency_number = mt_rand();
    $exchange_rates = [
      $this->randomMachineName() => mt_rand(),
      $this->randomMachineName() => mt_rand(),
      $this->randomMachineName() => mt_rand(),
    ];
    $rounding_step = mt_rand();
    $sign = $this->randomMachineName();
    $subunits = mt_rand();
    $status = TRUE;
    $label = $this->randomMachineName();

    $usage_start_a = mt_rand();
    $usage_end_a = mt_rand();
    $usage_country_code_a = $this->randomMachineName();
    $usage_start_b = mt_rand();
    $usage_end_b = mt_rand();
    $usage_country_code_b = $this->randomMachineName();
    $usage_start_c = mt_rand();
    $usage_end_c = mt_rand();
    $usage_country_code_c = $this->randomMachineName();
    /** @var \Commercie\Currency\Usage[] $usages */
    $usages = [
      (new Usage())->setStart($usage_start_a)->setEnd($usage_end_a)->setCountryCode($usage_country_code_a),
      (new Usage())->setStart($usage_start_b)->setEnd($usage_end_b)->setCountryCode($usage_country_code_b),
      (new Usage())->setStart($usage_start_c)->setEnd($usage_end_c)->setCountryCode($usage_country_code_c),
    ];

    $entity = Currency::create();
    $expected_array = [
      'uuid' => $entity->uuid(),
      'langcode' => 'en',
      'dependencies' => []
    ];
    $expected_array['alternativeSigns'] = $alternative_signs;
    $expected_array['currencyCode'] = $currency_code;
    $expected_array['currencyNumber'] = $currency_number;
    $expected_array['label'] = $label;
    $expected_array['roundingStep'] = $rounding_step;
    $expected_array['sign'] = $sign;
    $expected_array['subunits'] = $subunits;
    $expected_array['status'] = $status;
    $expected_array['usages'] = [
      [
        'start' => $usage_start_a,
        'end' => $usage_end_a,
        'countryCode' => $usage_country_code_a,
      ],
      [
        'start' => $usage_start_b,
        'end' => $usage_end_b,
        'countryCode' => $usage_country_code_b,
      ],
      [
        'start' => $usage_start_c,
        'end' => $usage_end_c,
        'countryCode' => $usage_country_code_c,
      ],
    ];

    $entity->setAlternativeSigns($expected_array['alternativeSigns']);
    $entity->setLabel($label);
    $entity->setUsages($usages);
    $entity->setSubunits($subunits);
    $entity->setRoundingStep($rounding_step);
    $entity->setSign($sign);
    $entity->setStatus($status);
    $entity->setCurrencyCode($currency_code);
    $entity->setCurrencyNumber($currency_number);

    $array = $entity->toArray();
    $this->assertEquals($expected_array, $array);
  }
}
