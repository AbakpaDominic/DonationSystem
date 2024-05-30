<?php

namespace Drupal\currency;

use Drupal\currency\Event\CurrencyEvents;
use Drupal\currency\Event\ResolveCountryCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

/**
 * Provides a Currency event dispatcher.
 */
class EventDispatcher implements EventDispatcherInterface {

  /**
   * The Symfony event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $symfonyEventDispatcher;

  /**
   * Constructs a new instance.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $symfony_event_dispatcher
   */
  public function __construct(SymfonyEventDispatcherInterface $symfony_event_dispatcher) {
    $this->symfonyEventDispatcher = $symfony_event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveCountryCode() {
    $event = new ResolveCountryCode();
    $this->symfonyEventDispatcher->dispatch($event,CurrencyEvents::RESOLVE_COUNTRY_CODE);

    return $event->getCountryCode();
  }

}
