<?php

namespace Drupal\currency\Plugin\Currency\AmountFormatter;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an amount formatter plugin manager.
 */
interface AmountFormatterManagerInterface extends PluginManagerInterface {

  /**
   * Gets the default plugin ID.
   *
   * @return string
   */
  public function getDefaultPluginId();

  /**
   * Sets the default plugin ID.
   *
   * @param string $plugin_id
   *
   * @return $this
   */
  public function setDefaultPluginId($plugin_id);

  /**
   * Gets the default formatter.
   *
   * @return \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterInterface
   */
  public function getDefaultPlugin();

  /**
   * Creates an amount formatter.
   *
   * @param string $plugin_id
   *   The id of the plugin being instantiated.
   * @param mixed[] $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterInterface
   */
  public function createInstance($plugin_id, array $configuration = array());

}
