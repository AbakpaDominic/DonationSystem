<?php

namespace Drupal\currency\Form;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface;
use Drupal\currency\PluginBasedExchangeRateProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the configuration form for \Drupal\currency\PluginBasedExchangeRateProvider.
 */
class PluginBasedExchangeRateProviderForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The plugin-based currency exchange rate provider.
   *
   * @var \Drupal\currency\PluginBasedExchangeRateProvider
   */
  protected $exchangeRateProvider;

  /**
   * The currency exchange rate provider manager.
   *
   * @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface
   */
  protected $currencyExchangeRateProviderManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\currency\PluginBasedExchangeRateProvider $exchange_rate_provider
   *   The currency exchange rate provider.
   * @param \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface $currency_exchange_rate_provider_manager
   *   The currency exchange rate provider manager.
   */
  public function __construct(TranslationInterface $string_translation, PluginBasedExchangeRateProvider $exchange_rate_provider, ExchangeRateProviderManagerInterface $currency_exchange_rate_provider_manager) {
    $this->exchangeRateProvider = $exchange_rate_provider;
    $this->currencyExchangeRateProviderManager = $currency_exchange_rate_provider_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('string_translation'), $container->get('currency.exchange_rate_provider'), $container->get('plugin.manager.currency.exchange_rate_provider'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'currency_exchange_rate_provider';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $definitions = $this->currencyExchangeRateProviderManager->getDefinitions();
    $configuration = $this->exchangeRateProvider->loadConfiguration();

    $form['exchange_rate_providers'] = array(
      '#header' => array($this->t('Title'), $this->t('Enabled'), $this->t('Weight'), $this->t('Operations')),
      '#tabledrag' => array(array(
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'form-select',
      )),
      '#type' => 'table',
    );
    $weight = 0;
    foreach ($configuration as $plugin_id => $enabled) {
      $weight++;
      $plugin_definition = $definitions[$plugin_id];

      $form['exchange_rate_providers'][$plugin_id] = array(
        '#attributes' => array(
          'class' => array('draggable'),
        ),
        '#weight' => $weight,
      );
      $form['exchange_rate_providers'][$plugin_id]['label'] = array(
        '#description' => $plugin_definition['description'],
        '#markup' => $plugin_definition['label'],
        '#title' => $this->t('Title'),
        '#title_display' => 'invisible',
        '#type' => 'item',
      );
      $form['exchange_rate_providers'][$plugin_id]['enabled'] = array(
        '#default_value' => $enabled,
        '#title' => $this->t('Enabled'),
        '#title_display' => 'invisible',
        '#type' => 'checkbox',
      );
      $form['exchange_rate_providers'][$plugin_id]['weight'] = array(
        '#default_value' => $weight,
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#type' => 'weight',
      );
      $operations_provider = $this->currencyExchangeRateProviderManager->getOperationsProvider($plugin_id);
      $form['exchange_rate_providers'][$plugin_id]['operations'] = array(
        '#links' => $operations_provider ? $operations_provider->getOperations($plugin_id) : [],
        '#title' => $this->t('Operations'),
        '#type' => 'operations',
      );
    }
    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['save'] = array(
      '#button_type' => 'primary',
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    uasort($values['exchange_rate_providers'], [SortArray::class, 'sortByWeightElement']);
    $configuration = array();
    foreach ($values['exchange_rate_providers'] as $plugin_id => $exchanger_configuration) {
      $configuration[$plugin_id] = (bool) $exchanger_configuration['enabled'];
    }
    $this->exchangeRateProvider->saveConfiguration($configuration);
    $this->messenger()->addMessage($this->t('The configuration options have been saved.'));
  }
}
