<?php

namespace Drupal\currency;

trait FormElementCallbackTrait {

  /**
   * Instantiates this class as a plugin and calls a method on it.
   */
  public static function __callStatic($name, array $arguments) {
    if (preg_match('/^instantiate#(.+?)#(.+?)$/', $name)) {
      list(, $method, $plugin_id) = explode('#', $name);
      /** @var \Drupal\Component\Plugin\PluginManagerInterface $element_info_manager */
      $element_info_manager = \Drupal::service('plugin.manager.element_info');
      /** @var \Drupal\currency\Element\CurrencyAmount $element_plugin */
      $element_plugin = $element_info_manager->createInstance($plugin_id);

      return call_user_func_array([$element_plugin, $method], $arguments);
    }
  }

}
