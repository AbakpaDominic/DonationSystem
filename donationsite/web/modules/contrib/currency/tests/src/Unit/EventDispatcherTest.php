<?php

namespace Drupal\Tests\currency\Unit;

use Drupal\currency\Event\CurrencyEvents;
use Drupal\currency\Event\ResolveCountryCode;
use Drupal\currency\EventDispatcher;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\currency\EventDispatcher
 *
 * @group Currency
 */
class EventDispatcherTest extends UnitTestCase {

  /**
   * The Symfony event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $symfonyEventDispatcher;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\EventDispatcher
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->symfonyEventDispatcher = $this->createMock(EventDispatcherInterface::class);

    $this->sut = new EventDispatcher($this->symfonyEventDispatcher);
  }

  /**
   * @covers ::resolveCountryCode
   */
  public function testResolveCountryCode() {;
    $this->symfonyEventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(ResolveCountryCode::class), CurrencyEvents::RESOLVE_COUNTRY_CODE);

    $this->sut->resolveCountryCode();
  }

}
