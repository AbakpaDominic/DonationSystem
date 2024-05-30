<?php

namespace Drupal\currency\Plugin\Currency\AmountFormatter;

use Drupal\Core\Url;
use Drupal\plugin\PluginType\DefaultPluginTypeOperationsProvider;

/**
 * Provides operations for the currency amount formatter plugin type.
 */
class AmountFormatterOperationsProvider extends DefaultPluginTypeOperationsProvider {

  /**
   * {@inheritdoc}
   */
  public function getOperations($plugin_type_id) {
    $operations = parent::getOperations($plugin_type_id);
    $operations['configure'] = [
      'title' => $this->t('Configure'),
      'url' => new Url('currency.amount_formatting'),
    ];

    return $operations;
  }

}
