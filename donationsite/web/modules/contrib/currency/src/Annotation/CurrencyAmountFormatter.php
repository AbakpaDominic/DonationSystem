<?php

namespace Drupal\currency\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a currency amount formatter plugin definition.
 *
 * If a plugin exposes configuration, it SHOULD also provide a configuration
 * schema for this configuration of which the name is
 * `plugin.plugin_configuration.currency_amount_formatter.[plugin_id]`,
 * where `[plugin_id]` is the plugin's ID.
 *
 * @Annotation
 */
class CurrencyAmountFormatter extends Plugin {

  /**
   * The translated human-readable plugin name (optional).
   *
   * @var string
   */
  public $description = '';

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The translated human-readable plugin name.
   *
   * @var string
   */
  public $label;

}
