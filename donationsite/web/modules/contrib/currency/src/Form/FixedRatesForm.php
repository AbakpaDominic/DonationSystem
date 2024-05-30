<?php

namespace Drupal\currency\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Entity\Currency as CurrencyEntity;
use Drupal\currency\FormHelperInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the configuration form for the currency_fixed_rates plugin.
 */
class FixedRatesForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * The currency exchange rate provider manager.
   *
   * @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface
   */
  protected $currencyExchangeRateProviderManager;

  /**
   * The form helper
   *
   * @var \Drupal\currency\FormHelperInterface
   */
  protected $formHelper;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   *   The currency storage.
   * @param \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface $currency_exchange_rate_provider_manager
   *   The currency exchange rate provider plugin manager.
   * @param \Drupal\currency\FormHelperInterface
   *   The form helper.
   */
  public function __construct(ConfigFactoryInterface $configFactory, TranslationInterface $string_translation, EntityStorageInterface $currency_storage, ExchangeRateProviderManagerInterface $currency_exchange_rate_provider_manager, FormHelperInterface $form_helper) {
    $this->setConfigFactory($configFactory);
    $this->currencyStorage = $currency_storage;
    $this->currencyExchangeRateProviderManager = $currency_exchange_rate_provider_manager;
    $this->formHelper = $form_helper;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static($container->get('config.factory'), $container->get('string_translation'), $entity_type_manager->getStorage('currency'), $container->get('plugin.manager.currency.exchange_rate_provider'), $container->get('currency.form_helper'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'currency_exchange_rate_provider_fixed_rates';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $currency_code_from = 'XXX', $currency_code_to = 'XXX') {
    $plugin = $this->currencyExchangeRateProviderManager->createInstance('currency_fixed_rates');
    $rate = $plugin->load($currency_code_from, $currency_code_to);

    $options = $this->formHelper->getCurrencyOptions();
    unset($options['XXX']);
    $form['currency_code_from'] = array(
      '#default_value' => $currency_code_from,
      '#disabled' => !is_null($rate),
      '#empty_value' => '',
      '#options' => $options,
      '#required' => TRUE,
      '#title' => $this->t('Source currency'),
      '#type' => 'select',
    );
    $form['currency_code_to'] = array(
      '#default_value' => $currency_code_to,
      '#disabled' => !is_null($rate),
      '#empty_value' => '',
      '#options' => $options,
      '#required' => TRUE,
      '#title' => $this->t('Destination currency'),
      '#type' => 'select',
    );
    $form['rate'] = array(
      '#limit_currency_codes' => array($currency_code_to),
      '#default_value' => array(
        'amount' => !is_null($rate) ? $rate->getRate() : NULL,
        'currency_code' => $currency_code_to,
      ),
      '#required' => TRUE,
      '#title' => $this->t('Exchange rate'),
      '#type' => 'currency_amount',
    );
    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['save'] = array(
      '#button_type' => 'primary',
      '#name' => 'save',
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    );
    if (!is_null($rate)) {
      $form['actions']['delete'] = array(
        '#button_type' => 'danger',
        '#limit_validation_errors' => array(array('currency_code_from'), array('currency_code_to')),
        '#name' => 'delete',
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\FixedRates $plugin */
    $plugin = $this->currencyExchangeRateProviderManager->createInstance('currency_fixed_rates');
    $values = $form_state->getValues();
    $currency_code_from = $values['currency_code_from'];
    $currency_code_to = $values['currency_code_to'];
    $currency_from = $this->currencyStorage->load($currency_code_from);
    $currency_to = $this->currencyStorage->load($currency_code_to);

    $triggering_element = $form_state->getTriggeringElement();
    switch ($triggering_element['#name']) {
      case 'save':
        $plugin->save($currency_code_from, $currency_code_to, $values['rate']['amount']);
        $this->messenger()->addMessage($this->t('The exchange rate for @currency_title_from to @currency_title_to has been saved.', array(
          '@currency_title_from' => $currency_from->label(),
          '@currency_title_to' => $currency_to->label(),
        )));
        break;
      case 'delete':
        $plugin->delete($currency_code_from, $currency_code_to);
        $this->messenger()->addMessage($this->t('The exchange rate for @currency_title_from to @currency_title_to has been deleted.', array(
          '@currency_title_from' => $currency_from->label(),
          '@currency_title_to' => $currency_to->label(),
        )));
        break;
    }
    $form_state->setRedirect('currency.exchange_rate_provider.fixed_rates.overview');
  }
}
