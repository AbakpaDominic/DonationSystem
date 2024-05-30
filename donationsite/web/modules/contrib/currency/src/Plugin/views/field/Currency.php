<?php

namespace Drupal\currency\Plugin\views\field;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Views field handler to get properties from currencies.
 *
 * This handler has one definition property:
 * - currency_method: the name of the method to call on
 *   \Drupal\currency\Entity\CurrencyInterface and of which to display the
 *   value, which must be a scalar.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("currency")
 */
class Currency extends FieldPluginBase {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   *   THe currency storage.
   *
   * @throws \InvalidArgumentException
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation, EntityStorageInterface $currency_storage) {
    if (!isset($configuration['currency_method'])) {
      throw new \InvalidArgumentException('Missing currency_method definition.');
    }
    elseif (!method_exists(CurrencyInterface::class, $configuration['currency_method'])) {
      throw new \InvalidArgumentException(sprintf('Method %s does not exist on \Drupal\currency\Entity\CurrencyInterface.', $configuration['currency_method']));
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currencyStorage = $currency_storage;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $entity_type_manager->getStorage('currency'));
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $currency_code = $this->getValue($values);
    $currency = $this->currencyStorage->load($currency_code);
    if ($currency) {
      return call_user_func([$currency, $this->configuration['currency_method']]);
    }
    else {
      return $this->t('Unknuwn currency %currency_code', array(
        '%currency_code' => $currency_code,
      ));
    }
  }

}
