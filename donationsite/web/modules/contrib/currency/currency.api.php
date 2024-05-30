<?php

/**
 * @file Contains Currency hook documentation.
 */

/**
 * Alters currency exchange rate provider plugins.
 *
 * @param array[] $definitions
 *   Keys are plugin IDs. Values are plugin definitions.
 */
function hook_currency_exchange_rate_provider_alter(array &$definitions) {
  // Remove an exchange rate provider plugin.
  unset($definitions['foo_plugin_id']);

  // Replace an exchange rate provider plugin with another.
  $definitions['foo_plugin_id']['class'] = 'Drupal\foo\FooExchangeRateProvider';
}

/**
 * Alters currency amount formatter plugins.
 *
 * @param array[] $definitions
 *   Keys are plugin IDs. Values are plugin definitions.
 */
function hook_currency_amount_formatter_alter(array &$definitions) {
  // Remove an amount formatter plugin.
  unset($definitions['foo_plugin_id']);

  // Replace an amount formatter plugin with another.
  $definitions['foo_plugin_id']['class'] = 'Drupal\foo\FooAmountFormatter';
}
