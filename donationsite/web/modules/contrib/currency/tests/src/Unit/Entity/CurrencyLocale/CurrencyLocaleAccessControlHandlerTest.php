<?php

namespace Drupal\Tests\currency\Unit\Entity\CurrencyLocale;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleAccessControlHandler;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Drupal\currency\LocaleResolverInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleAccessControlHandler
 *
 * @group Currency
 */
class CurrencyLocaleAccessControlHandlerTest extends UnitTestCase {

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheContextsManager;

  /**
   * Information about the entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityType;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleAccessControlHandler
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->cacheContextsManager = $this->getMockBuilder(CacheContextsManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->cacheContextsManager->expects($this->any())
      ->method('assertValidTokens')
      ->willReturn(TRUE);

    $container = new Container();
    $container->set('cache_contexts_manager', $this->cacheContextsManager);
    \Drupal::setContainer($container);

    $this->entityType = $this->createMock(EntityTypeInterface::class);

    $this->moduleHandler = $this->createMock(ModuleHandlerInterface::class);

    $this->sut = new CurrencyLocaleAccessControlHandler($this->entityType, $this->moduleHandler);
  }

  /**
   * @covers ::createInstance
   * @covers ::__construct
   */
  function testCreateInstance() {
    $container = $this->createMock(ContainerInterface::class);
    $container->expects($this->once())
      ->method('get')
      ->with('module_handler')
      ->willReturn($this->moduleHandler);

    $sut = CurrencyLocaleAccessControlHandler::createInstance($container, $this->entityType);
    $this->assertInstanceOf(CurrencyLocaleAccessControlHandler::class, $sut);
  }

  /**
   * @covers ::checkAccess
   *
   * @dataProvider providerTestCheckAccess
   */
  function testCheckAccess($expected_value, $operation, $has_permission, $permission, $locale = NULL) {
    $account = $this->createMock(AccountInterface::class);
    $account->expects($this->any())
      ->method('hasPermission')
      ->with($permission)
      ->willReturn((bool) $has_permission);

    $currency_locale = $this->createMock(CurrencyLocaleInterface::class);
    $currency_locale->expects($this->any())
      ->method('getLocale')
      ->willReturn($locale);

    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn([]);

    $method = new \ReflectionMethod($this->sut, 'checkAccess');
    $method->setAccessible(TRUE);

    $this->assertSame($expected_value, $method->invoke($this->sut, $currency_locale, $operation, $account)->isAllowed());
  }

  /**
   * Provides data to self::testCheckAccess().
   */
  function providerTestCheckAccess() {
    return array(
      // The default currency locale cannot be deleted, even with permission.
      array(FALSE, 'delete', TRUE, 'currency.currency_locale.delete', LocaleResolverInterface::DEFAULT_LOCALE),
      // Any non-default currency locale can be deleted with permission.
      array(TRUE, 'delete', TRUE, 'currency.currency_locale.delete', $this->randomMachineName()),
      // No currency locale can be deleted without permission.
      array(FALSE, 'delete', FALSE, 'currency.currency_locale.delete', $this->randomMachineName()),
      // Any currency locale can be updated with permission.
      array(TRUE, 'update', TRUE, 'currency.currency_locale.update', $this->randomMachineName()),
      // No currency locale can be updated without permission.
      array(FALSE, 'update', FALSE, 'currency.currency_locale.update', $this->randomMachineName()),
    );
  }

  /**
   * @covers ::checkCreateAccess
   *
   * @dataProvider providerTestCheckCreateAccess
   */
  function testCheckCreateAccess($expected_value, $has_permission) {
    $account = $this->createMock(AccountInterface::class);
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('currency.currency_locale.create')
      ->willReturn($has_permission);
    $context = array();

    $method = new \ReflectionMethod($this->sut, 'checkCreateAccess');
    $method->setAccessible(TRUE);

    $this->assertSame($expected_value, $method->invoke($this->sut, $account, $context)->isAllowed());
  }

  /**
   * Provides data to self::testCheckCreateAccess().
   */
  function providerTestCheckCreateAccess() {
    return array(
      array(TRUE, TRUE),
      array(FALSE, FALSE),
    );
  }

}
