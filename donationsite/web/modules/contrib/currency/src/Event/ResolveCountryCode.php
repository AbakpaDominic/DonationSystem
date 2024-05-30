<?php

namespace Drupal\currency\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Provides an event to resolve the current country code.
 *
 * @see \Drupal\currency\Event\CurrencyEvents::RESOLVE_COUNTRY_CODE
 */
class ResolveCountryCode extends Event {

  /**
   * The country code.
   *
   * @var string
   */
  protected $countryCode;

  /**
   * Sets the country code.
   *
   * @return $this
   */
  public function setCountryCode($country_code) {
    // We only want to stop propagation if an actual value was set. Setting an
    // empty value is allowed, but should not have consequences for event
    // propagation.
    if (!empty($country_code)) {
      $this->countryCode = $country_code;
      $this->stopPropagation();
    }

    return $this;
  }

  /**
   * Gets the country code.
   *
   * @return string
   */
  public function getCountryCode() {
    return $this->countryCode;
  }

}
