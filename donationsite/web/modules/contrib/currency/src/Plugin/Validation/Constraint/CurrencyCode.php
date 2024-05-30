<?php

namespace Drupal\currency\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Regex;

/**
 * Currency code constraint.
 *
 * @Plugin(
 *   id = "CurrencyCode",
 *   label = @Translation("Currency code"),
 *   type = { "string" }
 * )
 */
class CurrencyCode extends Regex {

  /**
   * {@inheritdoc}
   */
  public $message = '%currency_code is not a valid ISO 4217 currency code.';

  /**
   * {@inheritdoc}
   */
  public $pattern = '/^[A-Z]{3}$/i';
}
