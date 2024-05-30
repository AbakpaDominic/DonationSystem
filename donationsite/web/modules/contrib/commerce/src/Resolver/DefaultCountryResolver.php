<?php

namespace Drupal\commerce\Resolver;

use Drupal\commerce\Country;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Returns the site's default country.
 */
class DefaultCountryResolver implements CountryResolverInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new DefaultCountryResolver object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve() {
    $country_code = $this->configFactory->get('system.date')->get('country.default');
    if ($country_code && is_string($country_code)) {
      return new Country($country_code);
    }
  }

}
