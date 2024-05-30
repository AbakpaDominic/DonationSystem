<?php

namespace Drupal\Tests\currency\Unit\Plugin\Currency\ExchangeRateProvider;

use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\FixedRatesOperationsProvider;
use Drupal\Tests\plugin\Unit\OperationsProviderTestTrait;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\Currency\ExchangeRateProvider\FixedRatesOperationsProvider
 *
 * @group Currency
 */
class FixedRatesOperationsProviderTest extends UnitTestCase {

  use OperationsProviderTestTrait;

  /**
   * The redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $redirectDestination;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\FixedRatesOperationsProvider
   */
  protected $sut;

  public function setUp(): void {
    parent::setUp();

    $this->redirectDestination = $this->createMock(RedirectDestinationInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new FixedRatesOperationsProvider($this->stringTranslation, $this->redirectDestination);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->createMock(ContainerInterface::class);
    $map = [
      ['redirect.destination', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->redirectDestination],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    /** @var \Drupal\plugin\PluginType\DefaultPluginTypeOperationsProvider $sut_class */
    $sut_class = get_class($this->sut);
    $sut = $sut_class::create($container);
    $this->assertInstanceOf($sut_class, $sut);
  }

  /**
   * @covers ::getOperations
   */
  public function testGetOperations() {
    $this->assertOperationsLinks($this->sut->getOperations($this->randomMachineName()));
  }

}
