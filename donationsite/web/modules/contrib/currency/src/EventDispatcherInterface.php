<?php

namespace Drupal\currency;

/**
 * Defines a Currency event dispatcher.
 *
 * Because new events may be added in minor releases, this interface and all
 * classes that implement it are considered unstable forever. If you write an
 * event dispatcher, you must be prepared to update it in minor releases.
 */
interface EventDispatcherInterface {

  /**
   * Gets the current country code.
   *
   * @return string
   *   The current country code.
   */
  public function resolveCountryCode();

}
