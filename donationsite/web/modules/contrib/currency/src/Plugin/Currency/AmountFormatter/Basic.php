<?php

namespace Drupal\currency\Plugin\Currency\AmountFormatter;

use Commercie\Currency\CurrencyInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\LocaleResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Formats amounts using string translation and number_format().
 *
 * @CurrencyAmountFormatter(
 *   description = @Translation("Formats amounts using a translatable string."),
 *   id = "currency_basic",
 *   label = @Translation("Basic")
 * )
 */
class Basic extends PluginBase implements AmountFormatterInterface, ContainerFactoryPluginInterface {

  /**
   * The locale delegator.
   *
   * @var \Drupal\currency\LocaleResolverInterface
   */
  protected $localeDelegator;

  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed[] $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation_manager
   *   The translation manager.
   * @param \Drupal\currency\LocaleResolverInterface $locale_delegator
   *   The locale delegator.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $translation_manager, LocaleResolverInterface $locale_delegator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->localeDelegator = $locale_delegator;
    $this->stringTranslation = $translation_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $container->get('currency.locale_resolver'));
  }

  /**
   * {@inheritdoc}
   */
  public function formatAmount(CurrencyInterface $currency, $amount, $language_type = LanguageInterface::TYPE_CONTENT) {
    // Compute the number of decimals, so we can format all of them and no less
    // or more.
    $decimals = strlen($amount) - strpos($amount, '.') - 1;
    $currency_locale = $this->localeDelegator->resolveCurrencyLocale();
    $formatted_amount = number_format($amount, $decimals, $currency_locale->getDecimalSeparator(), $currency_locale->getGroupingSeparator());
    $arguments = array(
      '@currency_code' => $currency->getCurrencyCode(),
      '@currency_sign' => $currency->getSign(),
      '@amount' => $formatted_amount,
    );

    return $this->t('@currency_code @amount', $arguments);
  }
}
