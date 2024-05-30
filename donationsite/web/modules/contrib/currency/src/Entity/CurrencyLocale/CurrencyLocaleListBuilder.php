<?php

namespace Drupal\currency\Entity\CurrencyLocale;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list builder for currency locales.
 */
class CurrencyLocaleListBuilder extends ConfigEntityListBuilder {

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

    return new static($entity_type, $entity_type_manager->getStorage($entity_type->id()), $container->get('string_translation'), $container->get('module_handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row = array(
      'label' => $this->t('Locale'),
    ) + parent::buildHeader();

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = array(
      'label' => $entity->label(),
    ) + parent::buildRow($entity);

    return $row;
  }
}
