<?php

namespace Drupal\currency\Plugin\Currency\ExchangeRateProvider;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\plugin\PluginOperationsProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides operations for the fixed rates exchange rate provider.
 */
class FixedRatesOperationsProvider implements PluginOperationsProviderInterface, ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination.
   */
  public function __construct(TranslationInterface $string_translation, RedirectDestinationInterface $redirect_destination) {
    $this->redirectDestination = $redirect_destination;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('string_translation'), $container->get('redirect.destination'));
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($plugin_id) {
    $operations['configure'] = array(
      'title' => $this->t('Configure'),
      'query' => $this->redirectDestination->getAsArray(),
      'url' => new Url('currency.exchange_rate_provider.fixed_rates.overview'),
    );

    return $operations;
  }
}
