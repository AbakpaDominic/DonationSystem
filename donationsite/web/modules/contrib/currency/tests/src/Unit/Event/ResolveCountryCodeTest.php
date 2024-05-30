<?php

namespace Drupal\Tests\currency\Unit\Event;

use Drupal\currency\Event\ResolveCountryCode;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\Event\ResolveCountryCode
 *
 * @group Currency
 */
class ResolveCountryCodeTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Event\ResolveCountryCode
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->sut = new ResolveCountryCode();
  }

  /**
   * @covers ::setCountryCode
   * @covers ::getCountryCode
   */
  public function testGetCountryCode() {
    $country_code = $this->randomMachineName();

    $this->assertSame($this->sut, $this->sut->setCountryCode($country_code));
    $this->assertSame($country_code, $this->sut->getCountryCode());
  }

}
