<?php

namespace Drupal\currency\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Entity\CurrencyLocaleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles the "edit currency locale" route.
 */
class EditCurrencyLocale extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('string_translation'));
  }

  /**
   * Returns the title for a currency locale edit page.
   *
   * @param \Drupal\currency\Entity\CurrencyLocaleInterface $currency_locale
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function title(CurrencyLocaleInterface $currency_locale) {
    return $this->t('Edit @label', array(
      '@label' => $currency_locale->label(),
    ));
  }
}
