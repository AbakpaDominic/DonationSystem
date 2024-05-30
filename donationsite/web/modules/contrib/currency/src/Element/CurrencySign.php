<?php

namespace Drupal\currency\Element;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\FormElementCallbackTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an element to configure a currency's sign.
 *
 * @FormElement("currency_sign")
 */
class CurrencySign extends FormElement implements ContainerFactoryPluginInterface {

  use FormElementCallbackTrait;

  /**
   * The value for the currency_sign form element's "custom" option.
   */
  const CUSTOM_VALUE = '###CUSTOM###';

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
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation, EntityStorageInterface $currency_storage) {
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
  public function getInfo() {
    $plugin_id = $this->getPluginId();

    return [
      // The ISO 4217 code of the currency which signs to suggest to the user.
      // Optional.
      '#currency_code' => FALSE,
      '#element_validate' => [[get_class($this), 'elementValidate']],
      '#process' => [[get_class($this), 'instantiate#process#' . $plugin_id]],
    ];
  }

  /**
   * Implements form #process callback.
   */
  public function process(array $element, FormStateInterface $form_state, array $form) {
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = NULL;
    if ($element['#currency_code']) {
      $currency = $this->currencyStorage->load($element['#currency_code']);
    }
    if (!$currency) {
      $currency = $this->currencyStorage->load('XXX');
    }

    // Modify the element.
    $element['#tree'] = TRUE;
    $element['#theme_wrappers'][] = 'form_element';
    $element['#attached']['library'] = ['currency/element.currency_sign'];

    $signs = array_merge(array($currency->getSign()), $currency->getAlternativeSigns());
    $signs = array_combine($signs, $signs);
    $signs = array_unique(array_filter(array_merge(array(
      self::CUSTOM_VALUE => t('- Custom -'),
    ), $signs)));
    asort($signs);
    $element['sign'] = array(
      '#default_value' => in_array($element['#default_value'], $signs) ? $element['#default_value'] : self::CUSTOM_VALUE,
      '#empty_value' => '',
      '#options' => $signs,
      '#required' => $element['#required'],
      '#title' => t('Sign'),
      '#title_display' => 'invisible',
      '#type' => 'select',
    );
    $sign_js_selector = '.form-type-currency-sign .form-select';
    $element['sign_custom'] = array(
      '#default_value' => $element['#default_value'],
      '#states' => array(
        'visible' => array(
          $sign_js_selector => array(
            'value' => self::CUSTOM_VALUE,
          ),
        ),
      ),
      '#title' => t('Custom sign'),
      '#title_display' => 'invisible',
      '#type' => 'textfield',
    );

    return $element;
  }

  /**
   * Implements form #element_validate callback.
   */
  public static function elementValidate($element, FormStateInterface $form_state, $form) {
    // Set a scalar value.
    $sign = $element['#value']['sign'];
    if ($sign == self::CUSTOM_VALUE) {
      $sign = $element['#value']['sign_custom'];
    }
    $form_state->setValueForElement($element, $sign);
  }

}
