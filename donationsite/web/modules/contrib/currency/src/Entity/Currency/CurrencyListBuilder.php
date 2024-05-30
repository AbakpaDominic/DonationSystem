<?php

namespace Drupal\currency\Entity\Currency;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list builder for currencies.
 */
class CurrencyListBuilder extends ConfigEntityListBuilder {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $entity_storage, TranslationInterface $string_translation, ModuleHandlerInterface $module_handler) {
    parent::__construct($entity_type, $entity_storage);
    $this->moduleHandler = $module_handler;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static($entity_type, $entity_type_manager->getStorage('currency'), $container->get('string_translation'), $container->get('module_handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = array(
      'id' => $this->t('Currency code'),
      'label' => $this->t('Name'),
    ) + parent::buildHeader();

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = array(
      'id' => $entity->id(),
      'label' => $entity->label(),
    ) + parent::buildRow($entity);

    return $row;
  }
}
