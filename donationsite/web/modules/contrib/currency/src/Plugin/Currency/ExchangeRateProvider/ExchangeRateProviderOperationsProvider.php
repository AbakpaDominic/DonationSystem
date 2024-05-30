<?php

namespace Drupal\currency\Plugin\Currency\ExchangeRateProvider;

use Drupal\Core\Url;
use Drupal\plugin\PluginType\DefaultPluginTypeOperationsProvider;

/**
 * Provides operations for the currency exchange rate provider plugin type.
 */
class ExchangeRateProviderOperationsProvider extends DefaultPluginTypeOperationsProvider {

  /**
   * {@inheritdoc}
   */
  public function getOperations($plugin_type_id) {
    $operations = parent::getOperations($plugin_type_id);
    $operations['configure'] = [
      'title' => $this->t('Configure'),
      'url' => new Url('currency.exchange_rate_provider.config'),
    ];

    return $operations;
  }

}
