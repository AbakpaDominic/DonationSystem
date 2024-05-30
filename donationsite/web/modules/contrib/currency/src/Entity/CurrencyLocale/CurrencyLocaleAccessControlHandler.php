<?php

namespace Drupal\currency\Entity\CurrencyLocale;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\currency\LocaleResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks access for currency_locale entities.
 */
class CurrencyLocaleAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * Constructor
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity info for the entity type.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityTypeInterface $entity_type, ModuleHandlerInterface $module_handler) {
    parent::__construct($entity_type);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static($entity_type, $container->get('module_handler'));
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $currency_locale, $operation, AccountInterface $account) {
    /** @var \Drupal\currency\Entity\CurrencyLocaleInterface $currency_locale */
    if ($currency_locale->getLocale() == LocaleResolverInterface::DEFAULT_LOCALE && $operation == 'delete') {
      return AccessResult::forbidden();
    }
    return AccessResult::allowedIfHasPermission($account, 'currency.currency_locale.' . $operation);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'currency.currency_locale.create');
  }
}
