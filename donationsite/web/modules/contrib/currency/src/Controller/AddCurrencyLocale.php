<?php

namespace Drupal\currency\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles the "add currency locale" route.
 */
class AddCurrencyLocale extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The currency locale storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyLocaleStorage;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_locale_storage
   *   The currency locale storage.
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder, EntityStorageInterface $currency_locale_storage) {
    $this->currencyLocaleStorage = $currency_locale_storage;
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static($container->get('entity.form_builder'), $entity_type_manager->getStorage('currency_locale'));
  }

  /**
   * Builds a currency add form.
   *
   * @return array
   *   A renderable array.
   */
  public function execute() {
    $currency_locale = $this->currencyLocaleStorage->create([]);

    return $this->entityFormBuilder->getForm($currency_locale);
  }

}
