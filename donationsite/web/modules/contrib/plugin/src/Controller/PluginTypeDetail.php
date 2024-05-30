<?php

namespace Drupal\plugin\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\plugin\PluginType\PluginTypeInterface;
use Drupal\plugin\PluginType\PluginTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles the "plugin type detail" route.
 */
class PluginTypeDetail implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The plugin type manager.
   *
   * @var \Drupal\plugin\PluginType\PluginTypeManagerInterface
   */
  protected $pluginTypeManager;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.plugin_type_manager'),
      $container->get('string_translation'),
    );
  }

  /**
   * Creates a PluginTypeDetail instance.
   *
   * @param \Drupal\plugin\PluginType\PluginTypeManagerInterface $plugin_type_manager
   *   The plugin type manager.
   * @param \Drupal\Core\StringTranslation\TranslationManager $string_translation
   *   The string translation service.
   */
  public function __construct(
    PluginTypeManagerInterface $plugin_type_manager,
    TranslationManager $string_translation
  ) {
    $this->pluginTypeManager = $plugin_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Returns the route's title.
   *
   * @param \Drupal\plugin\PluginType\PluginTypeInterface $plugin_type
   *   The plugin type.
   *
   * @return string
   */
  public function title(PluginTypeInterface $plugin_type) {
    return $this->t('%label plugin type', [
      '%label' => $plugin_type->getLabel(),
    ]);
  }

  /**
   * Callback for the plugin.plugin_type.detail route.
   *
   * @param \Drupal\plugin\PluginType\PluginTypeInterface $plugin_type
   *   The plugin type.
   *
   */
  public function content(PluginTypeInterface $plugin_type) {
    if (\Drupal::getContainer()->has('devel.dumper')) {
      return \Drupal::service('devel.dumper')->exportAsRenderable($plugin_type, $plugin_type->getId());
    }
    else {
      // @todo Do something if Devel is absent.
      return [];
    }
  }

}
