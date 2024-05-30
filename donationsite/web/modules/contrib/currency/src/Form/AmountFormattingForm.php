<?php

namespace Drupal\currency\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Radios;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configures amount formatting.
 */
class AmountFormattingForm extends ConfigFormBase {

  /**
   * The currency amount formatter manager.
   *
   * @var \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface
   */
  protected $currencyAmountFormatterManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface $currency_amount_formatter_manager
   *   The currency amount formatter manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TranslationInterface $string_translation, AmountFormatterManagerInterface $currency_amount_formatter_manager) {
    $this->setConfigFactory($config_factory);
    $this->currencyAmountFormatterManager = $currency_amount_formatter_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('string_translation'), $container->get('plugin.manager.currency.amount_formatter'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'currency_amount_formatting';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['currency.amount_formatting'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('currency.amount_formatting');

    $options = array();
    foreach ($this->currencyAmountFormatterManager->getDefinitions() as $plugin_id => $plugin_definition) {
      $options[$plugin_id] = $plugin_definition['label'];
    }
    $form['default_plugin_id'] = array(
      '#default_value' => $config->get('plugin_id'),
      '#options' => $options,
      '#process' => [[Radios::class, 'processRadios'], [$this, 'processPluginOptions']],
      '#title' => $this->t('Default amount formatter'),
      '#type' => 'radios',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements Form API #process callback.
   */
  public function processPluginOptions($element) {
    foreach ($this->currencyAmountFormatterManager->getDefinitions() as $plugin_id => $plugin_definition) {
      if (isset($plugin_definition['description'])) {
        $element[$plugin_id]['#description'] = $plugin_definition['description'];
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('currency.amount_formatting');
    $config->set('plugin_id', $values['default_plugin_id']);
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
