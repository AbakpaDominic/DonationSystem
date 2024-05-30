<?php

namespace Drupal\currency\Plugin\views\filter;

use Drupal\currency\Entity\Currency as CurrencyEntity;
use Drupal\currency\FormHelperInterface;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Views filter handler to filter currencies by ISO 4217 code.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("currency")
 */
class Currency extends InOperator {

  /**
   * The form helper
   *
   * @var \Drupal\currency\FormHelperInterface
   */
  protected $formHelper;

  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\currency\FormHelperInterface
   *   The form helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormHelperInterface $form_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formHelper = $form_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('currency.form_helper'));
  }

  /**
   * {@inheritdoc}
   */
  function getValueOptions() {
    if (is_null($this->valueOptions)) {
      $this->valueOptions = $this->formHelper->getCurrencyOptions();
    }

    return $this->valueOptions;
  }
}
