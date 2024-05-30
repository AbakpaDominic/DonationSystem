<?php

namespace Drupal\currency\Entity\Currency;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks access for currency entities.
 */
class CurrencyAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
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
  protected function checkAccess(EntityInterface $currency, $operation, AccountInterface $account) {
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */

    // Don't let the default currency be deleted.
    if ($currency->getCurrencyCode() == 'XXX' && $operation == 'delete') {
      return AccessResult::forbidden();
    }

    // The "enable" and "disable" operations are aliases for "update", but with
    // extra checks.
    if ($operation == 'enable') {
      return $currency->status() ? AccessResult::forbidden() : $this->access($currency, 'update', $account, TRUE);
    }
    if ($operation == 'disable') {
      return $currency->status() ? $this->access($currency, 'update', $account, TRUE) : AccessResult::forbidden();
    }

    return AccessResult::allowedIfHasPermission($account, 'currency.currency.' . $operation);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'currency.currency.create');
  }
}
