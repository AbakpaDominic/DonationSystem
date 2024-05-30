<?php

namespace Drupal\Tests\currency\Unit\Controller;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\currency\Controller\EnableCurrency;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversDefaultClass \Drupal\currency\Controller\EnableCurrency
 *
 * @group Currency
 */
class EnableCurrencyTest extends UnitTestCase {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $urlGenerator;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Controller\EnableCurrency
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

    $this->sut = new EnableCurrency($this->urlGenerator);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->createMock(ContainerInterface::class);
    $map = array(
      array('url_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->urlGenerator),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = EnableCurrency::create($container);
    $this->assertInstanceOf(EnableCurrency::class, $sut);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $url = $this->randomMachineName();

    $currency = $this->createMock(CurrencyInterface::class);
    $currency->expects($this->once())
      ->method('enable');
    $currency->expects($this->once())
      ->method('save');

    $this->urlGenerator->expects($this->once())
      ->method('generateFromRoute')
      ->with('entity.currency.collection')
      ->willReturn($url);

    $response = $this->sut->execute($currency);
    $this->assertInstanceOf(RedirectResponse::class, $response);
    $this->assertSame($url, $response->getTargetUrl());
  }

}
