<?php

namespace Drupal\currency\Element;

use Commercie\Currency\InputInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Entity\Currency;
use Drupal\currency\FormElementCallbackTrait;
use Drupal\currency\FormHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an element to collect amounts of money and convert them to strings.
 *
 * @FormElement("currency_amount")
 */
class CurrencyAmount extends FormElement implements ContainerFactoryPluginInterface {

  use FormElementCallbackTrait;

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * The form helper.
   *
   * @var \Drupal\currency\FormHelperInterface
   */
  protected $formHelper;

  /**
   * The input parser.
   *
   * @var \Commercie\Currency\InputInterface
   */
  protected $input;

  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   * @param \Commercie\Currency\InputInterface $input
   * @param \Drupal\currency\FormHelperInterface $form_helper
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation, EntityStorageInterface $currency_storage, InputInterface $input, FormHelperInterface $form_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currencyStorage = $currency_storage;
    $this->formHelper = $form_helper;
    $this->input = $input;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $entity_type_manager->getStorage('currency'), $container->get('currency.input'), $container->get('currency.form_helper'));
  }

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $plugin_id = $this->getPluginId();

    return [
      '#default_value' => [
        'amount' => NULL,
        'currency_code' => NULL,
      ],
      '#element_validate' => [[get_class($this), 'instantiate#elementValidate#' . $plugin_id]],
      // The ISO 4217 codes of the currencies the amount must be in. Use an empty
      // array to allow any currency.
      '#limit_currency_codes' => [],
      // The minimum amount as a numeric string, or FALSE to omit.
      '#minimum_amount' => FALSE,
      // The maximum amount as a numeric string, or FALSE to omit.
      '#maximum_amount' => FALSE,
      '#process' => [[get_class($this), 'instantiate#process#' . $plugin_id]],
    ];
  }

  /**
   * Implements form #process callback.
   */
  public function process(array $element, FormStateInterface $form_state, array $form) {
    // Validate element configuration.
    if ($element['#minimum_amount'] !== FALSE && !is_numeric($element['#minimum_amount'])) {
      throw new \InvalidArgumentException('The minimum amount must be a number.');
    }
    if ($element['#maximum_amount'] !== FALSE && !is_numeric($element['#maximum_amount'])) {
      throw new \InvalidArgumentException('The maximum amount must be a number.');
    }
    if (!is_array($element['#limit_currency_codes'])) {
      throw new \InvalidArgumentException('#limit_currency_codes must be an array.');
    }
    if ($element['#limit_currency_codes']
      && $element['#default_value']['currency_code']
      && !in_array($element['#default_value']['currency_code'], $element['#limit_currency_codes'])) {
      throw new \InvalidArgumentException('The default currency is not in the list of allowed currencies.');
    }

    // Load the default currency.
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = NULL;
    if ($element['#default_value']['currency_code']) {
      $currency = $this->currencyStorage->load($element['#default_value']['currency_code']);
    }
    if(!$currency) {
      $currency = $this->currencyStorage->load('XXX');
    }

    // Modify the element.
    $element['#tree'] = TRUE;
    $element['#theme_wrappers'][] = 'form_element';
    $element['#attached']['library'] = ['currency/element.currency_amount'];

    // Add the currency element.
    if (count($element['#limit_currency_codes']) == 1) {
      $element['currency_code'] = [
        '#value' => reset($element['#limit_currency_codes']),
        '#type' => 'value',
      ];
    }
    else {
      $element['currency_code'] = [
        '#default_value' => $currency->id(),
        '#type' => 'select',
        '#title' => $this->t('Currency'),
        '#title_display' => 'invisible',
        '#options' => $element['#limit_currency_codes'] ? array_intersect_key($this->formHelper->getCurrencyOptions(), array_flip($element['#limit_currency_codes'])) : $this->formHelper->getCurrencyOptions(),
        '#required' => $element['#required'],
      ];
    }

    // Add the amount element.
    $description = NULL;
    if ($element['#minimum_amount'] !== FALSE) {
      $description = $this->t('The minimum amount is @amount.', [
        '@amount' => $currency->formatAmount($element['#minimum_amount']),
      ]);
    }
    $element['amount'] = [
      '#default_value' => $element['#default_value']['amount'],
      '#type' => 'textfield',
      '#title' => $this->t('Amount'),
      '#title_display' => 'invisible',
      '#description' => $description,
      '#prefix' => count($element['#limit_currency_codes']) == 1 ? $currency->getSign() : NULL,
      '#required' => $element['#required'],
      '#size' => 9,
    ];

    return $element;
  }

  /**
   * Implements form #element_validate callback.
   */
  public function elementValidate($element, FormStateInterface $form_state, array $form) {
    $values = $form_state->getValues();
    $value = NestedArray::getValue($values, $element['#parents']);
    $amount = $value['amount'];
    $currency_code = $value['currency_code'];

    // Confirm that the amount is numeric.
    $amount = $this->input->parseAmount($amount);
    if ($amount === FALSE) {
      $form_state->setError($element['amount'], $this->t('%title is not numeric.', [
        '%title' => $element['#title'],
      ]));
    }

    // Confirm the amount lies within the allowed range.
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = $this->currencyStorage->load($currency_code);
    if ($element['#minimum_amount'] !== FALSE && bccomp($element['#minimum_amount'], $amount, 6) > 0) {
      $form_state->setError($element['amount'], $this->t('The minimum amount is @amount.', [
        '@amount' => $currency->formatAmount($element['#minimum_amount']),
      ]));
    }
    elseif ($element['#maximum_amount'] !== FALSE && bccomp($amount, $element['#maximum_amount'], 6) > 0) {
      $form_state->setError($element['amount'], $this->t('The maximum amount is @amount.', [
        '@amount' => $currency->formatAmount($element['#maximum_amount']),
      ]));
    }

    // The amount in $form_state is a human-readable, optionally localized
    // string, which cannot be used by other code. $amount is a numeric string
    // after running it through \Drupal::service('currency.input')->parseAmount().
    $form_state->setValueForElement($element, [
      'amount' => $amount,
      'currency_code' => $currency_code,
    ]);
  }

}
