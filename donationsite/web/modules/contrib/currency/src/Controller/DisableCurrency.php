<?php

namespace Drupal\currency\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\currency\Entity\CurrencyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Handles the "disable currency" route.
 */
class DisableCurrency extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   */
  public function __construct(UrlGeneratorInterface $url_generator) {
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('url_generator'));
  }

  /**
   * Disables a currency.
   *
   * @param \Drupal\currency\Entity\CurrencyInterface $currency
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function execute(CurrencyInterface $currency) {
    $currency->disable();
    $currency->save();

    return new RedirectResponse($this->urlGenerator->generateFromRoute('entity.currency.collection', array(
      'absolute' => TRUE,
    )));
  }

}
