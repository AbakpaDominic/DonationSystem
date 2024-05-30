<?php

namespace Drupal\currency\Plugin\views\field;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Views field handler for currency amounts.
 *
 * This handler has two definition properties:
 * - currency_code
 * - currency_code_field
 * - currency_code_table
 * See self::defaultDefinition() for a detailed explanation.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("currency_amount")
 */
class Amount extends FieldPluginBase implements ContainerFactoryPluginInterface {

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Render\RendererInterface
   *   The renderer.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   *   THe currency storage.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation, ModuleHandlerInterface $module_handler, RendererInterface $renderer, EntityStorageInterface $currency_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->definition += $this->defaultDefinition();
    $this->currencyStorage = $currency_storage;
    $this->moduleHandler = $module_handler;
    $this->renderer = $renderer;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $container->get('module_handler'), $container->get('renderer'), $entity_type_manager->getStorage('currency'));
  }

  /**
   * Returns default definition values.
   *
   * @return mixed[]
   */
  protected function defaultDefinition() {
    return array(
      // A default currency code to use for the amounts.
      'currency_code' => 'XXX',
      // The name of the database field the currency code is in.
      'currency_code_field' => NULL,
      // The name of the database table currency_field is in. Defaults to the
      // same table this handler is used on.
      'currency_code_table' => NULL,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    if ($this->definition['currency_code_field']) {
      $this->addAdditionalFields(array(
        'currency_code_field' => array(
          'field' => $this->definition['currency_code_field'],
          'table' => $this->definition['currency_code_table'] ? $this->definition['currency_code_table'] : $this->tableAlias,
        ),
      ));
    }
    parent::query();
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();

    // Whether to round amounts.
    $options['currency_round'] = array(
      'default' => FALSE,
    );

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['currency_round'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Round amounts based on their currencies'),
      '#default_value' => $this->options['currency_round'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $currency = $this->getCurrency($values);
    $amount = $this->getAmount($values);

    return $currency->formatAmount($amount, $this->options['currency_round']);
  }

  /**
   * Loads the Currency for this field.
   *
   * @throws \RuntimeException
   *
   * @param \Drupal\views\ResultRow $values
   *   A values object as received by $this->render().
   *
   * @return \Drupal\currency\Entity\CurrencyInterface
   */
  protected function getCurrency(ResultRow $values) {
    $currency = NULL;

    if ($this->definition['currency_code_field']) {
      $currency_code = $this->getValue($values, 'currency_code_field');
      if ($currency_code) {
        $currency = $this->currencyStorage->load($currency_code);
      }
    }
    if (!$currency && $this->definition['currency_code']) {
      $currency = $this->currencyStorage->load($this->definition['currency_code']);
    }
    if (!$currency) {
      $currency = $this->currencyStorage->load('XXX');
    }
    if (!$currency) {
      throw new \RuntimeException('Could not load currency XXX.');
    }

    return $currency;
  }

  /**
   * Gets this field's amount.
   *
   * If the amount cannot be fetched from your implementation's database field
   * as a numeric string, you should override this method so it returns a
   * numeric/decimal representation of the amount.
   *
   * @param \Drupal\views\ResultRow $values
   *   A values object as received by $this->render().
   *
   * @return string
   *   A numeric string.
   */
  protected function getAmount(ResultRow $values) {
    return $this->getValue($values);
  }

}
