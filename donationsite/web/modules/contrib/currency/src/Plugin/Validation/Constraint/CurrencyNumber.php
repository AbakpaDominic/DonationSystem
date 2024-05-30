<?php

namespace Drupal\currency\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Regex;

/**
 * Currency number constraint.
 *
 * @Plugin(
 *   id = "CurrencyNumber",
 *   label = @Translation("Currency number"),
 *   type = { "string" }
 * )
 */
class CurrencyNumber extends Regex {

  /**
   * {@inheritdoc}
   */
  public $message = '%currency_number is not a valid ISO 4217 currency number.';

  /**
   * {@inheritdoc}
   */
  public $pattern = '/^[\d]{3}$/i';
}
