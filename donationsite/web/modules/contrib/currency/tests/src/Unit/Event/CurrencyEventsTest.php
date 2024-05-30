<?php

namespace Drupal\Tests\currency\Unit\Event;

use Drupal\currency\Event\CurrencyEvents;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\Event\CurrencyEvents
 *
 * @group Currency
 */
class CurrencyEventsTest extends UnitTestCase {

  /**
   * Tests constants with event names.
   */
  public function testEventNames() {
    $class = new \ReflectionClass(CurrencyEvents::class);
    foreach ($class->getConstants() as $event_name) {
      // Make sure that every event name is properly namespaced.
      $this->assertSame(0, strpos($event_name, 'drupal.currency.'));
    }
  }

}
