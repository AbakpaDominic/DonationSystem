<?php

namespace Drupal\currency\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the overview of fixed exchange rates.
 */
class FixedRatesOverview extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The currency amount formatter manager.
   *
   * @var \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface
   */
  protected $currencyAmountFormatterManager;

  /**
   * The currency exchange rate provider manager.
   *
   * @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface
   */
  protected $currencyExchangeRateProviderManager;

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation_manager
   *   The translation manager.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   *   The currency storage.
   * @param \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface $currency_amount_formatter_manager
   *   The currency locale delegator.
   * @param \Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderManagerInterface $currency_exchange_rate_provider_manager
   *   The currency exchanger plugin manager.
   */
  public function __construct(TranslationInterface $translation_manager, UrlGeneratorInterface $url_generator, EntityStorageInterface $currency_storage, AmountFormatterManagerInterface $currency_amount_formatter_manager, ExchangeRateProviderManagerInterface $currency_exchange_rate_provider_manager) {
    $this->currencyStorage = $currency_storage;
    $this->currencyAmountFormatterManager = $currency_amount_formatter_manager;
    $this->currencyExchangeRateProviderManager = $currency_exchange_rate_provider_manager;
    $this->stringTranslation = $translation_manager;
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    return new static($container->get('string_translation'), $container->get('url_generator'), $entity_type_manager->getStorage('currency'), $container->get('plugin.manager.currency.amount_formatter'), $container->get('plugin.manager.currency.exchange_rate_provider'));
  }

  /**
   * Views the configured fixed rates.
   *
   * @return array
   *   A renderable array.
   */
  public function overview() {
    /** @var \Drupal\currency\Plugin\Currency\ExchangeRateProvider\FixedRates $plugin */
    $plugin = $this->currencyExchangeRateProviderManager->createInstance('currency_fixed_rates');
    $rates = $plugin->loadALl();

    $form['rates'] = array(
      '#empty' => $this->t('There are no exchange rates yet. <a href="@path">Add an exchange rate</a>.', array(
        '@path' => $this->urlGenerator->generateFromRoute('currency.exchange_rate_provider.fixed_rates.add'),
      )),
      '#header' => array($this->t('From'), $this->t('To'), $this->t('Exchange rate'), $this->t('Operations')),
      '#type' => 'table',
    );
    foreach ($rates as $currency_code_from => $currency_codes_to) {
      foreach ($currency_codes_to as $currency_code_to => $rate) {
        $currency_from = $this->currencyStorage->load($currency_code_from);
        $currency_to = $this->currencyStorage->load($currency_code_to);
        if ($currency_from && $currency_to) {
          $row['currency_from'] = array(
            '#markup' => $currency_from->label(),
            '#type' => 'item',
          );
          $row['currency_to'] = array(
            '#markup' => $currency_to->label(),
            '#type' => 'item',
          );
          $row['rate'] = array(
            '#markup' => $this->currencyAmountFormatterManager->getDefaultPlugin()->formatAmount($currency_to, $rate),
            '#type' => 'item',
          );
          $row['operations'] = array(
            '#links' => array(array(
              'title' => $this->t('edit'),
              'route_name' => 'currency.exchange_rate_provider.fixed_rates.edit',
              'route_parameters' => array(
                'currency_code_from' => $currency_code_from,
                'currency_code_to' => $currency_code_to,
              ),
            )),
            '#type' => 'operations',
          );
          $form['rates'][] = $row;
        }
      }
    }

    return $form;
  }
}
