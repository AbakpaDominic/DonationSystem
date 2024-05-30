<?php

namespace Drupal\currency\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Entity\CurrencyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles the "edit currency" route.
 */
class EditCurrency extends ControllerBase implements ContainerInjectionInterface {

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
   * Returns the title for a currency edit page.
   *
   * @param \Drupal\currency\Entity\CurrencyInterface $currency
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function title(CurrencyInterface $currency) {
    return $this->t('Edit @label', array(
      '@label' => $currency->label(),
    ));
  }

}
