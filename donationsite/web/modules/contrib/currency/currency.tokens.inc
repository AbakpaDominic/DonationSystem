<?php

/**
 * @file
 * Contains Token API hook implementations.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function currency_token_info() {
  // Token groups.
  $types['currency'] = array(
    'name' => t('Currencies'),
    'needs-data' => 'currency',
  );

  // Tokens.
  $tokens['currency']['code'] = array(
    'description' => t('The (ISO 4217) currency code.'),
    'name' => t('Currency code'),
    'type' => 'text',
  );
  $tokens['currency']['number'] = array(
    'description' => t('The ISO 4217 currency number.'),
    'name' => t('Currency number'),
    'type' => 'text',
  );
  $tokens['currency']['subunits'] = array(
    'description' => t('The number of subunits.'),
    'name' => t('Subunits'),
    'type' => 'text',
  );
  $tokens['currency']['sign'] = array(
    'name' => t('Sign'),
    'type' => 'text',
  );
  $tokens['currency']['title'] = array(
    'name' => t('Name'),
    'type' => 'text',
  );

  return array(
    'tokens' => $tokens,
    'types' => $types,
  );
}

/**
 * Implements hook_tokens().
 */
function currency_tokens($type, array $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = array();

  if ($type == 'currency' && isset($data['currency'])) {
    $currency_code = $data['currency'];
    if (isset($tokens['code'])) {
      $replacements[$tokens['code']] = $currency_code;
      unset($tokens['code']);
    }
    if ($tokens) {
      /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
      $currency = \Drupal::entityTypeManager()->getStorage('currency')->load($currency_code);
      if ($currency) {
        $bubbleable_metadata->addCacheableDependency($currency);
        foreach ($tokens as $token => $original) {
          switch ($token) {
            case 'number':
              $replacements[$original] = $currency->getCurrencyNumber();
              break;

            case 'sign':
              $replacements[$original] = $currency->getSign();
              break;

            case 'subunits':
              $replacements[$original] = $currency->getSubunits();
              break;

            case 'title':
              $replacements[$original] = $currency->label();
              break;
          }
        }
      }
    }
  }

  return $replacements;
}
