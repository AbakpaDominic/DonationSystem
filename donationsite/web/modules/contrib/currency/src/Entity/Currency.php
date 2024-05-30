<?php

namespace Drupal\currency\Entity;

use Commercie\Currency\Usage;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface;

/**
 * Defines a currency entity class.
 *
 * @ConfigEntityType(
 *   handlers = {
 *     "access" = "Drupal\currency\Entity\Currency\CurrencyAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\currency\Entity\Currency\CurrencyForm",
 *       "delete" = "Drupal\currency\Entity\Currency\CurrencyDeleteForm"
 *     },
 *     "list_builder" = "Drupal\currency\Entity\Currency\CurrencyListBuilder",
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *   },
 *   entity_keys = {
 *     "id" = "currencyCode",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "alternativeSigns",
 *     "currencyCode",
 *     "currencyNumber",
 *     "label",
 *     "roundingStep",
 *     "sign",
 *     "subunits",
 *     "status",
 *     "usages",
 *     "uuid",
 *   },
 *   id = "currency",
 *   label = @Translation("Currency"),
 *   links = {
 *     "collection" = "/admin/config/regional/currency",
 *     "create-form" = "/admin/config/regional/currency/add",
 *     "edit-form" = "/admin/config/regional/currency/{currency}",
 *     "delete-form" = "/admin/config/regional/currency/{currency}/delete"
 *   }
 * )
 */
class Currency extends ConfigEntityBase implements CurrencyInterface {

  /**
   * Alternative (non-official) currency signs.
   *
   * @var array
   *   An array of strings that are similar to self::sign.
   */
  protected $alternativeSigns = [];

  /**
   * The currency amount formatter manager.
   *
   * @var \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface
   */
  protected $currencyAmountFormatterManager;

  /**
   * ISO 4217 currency code.
   *
   * @var string
   */
  public $currencyCode;

  /**
   * ISO 4217 currency number.
   *
   * @var string
   */
  protected $currencyNumber;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The human-readable name.
   *
   * @var string
   */
  public $label;

  /**
   * The number of subunits to round amounts in this currency to.
   *
   * @see Currency::getRoundingStep()
   *
   * @var integer
   */
  protected $roundingStep;

  /**
   * The currency's official sign, such as '€' or '$'.
   *
   * @var string
   */
  protected $sign = '¤';

  /**
   * The number of subunits this currency has.
   *
   * @var integer|null
   */
  protected $subunits;

  /**
   * This currency's usages.
   *
   * @var \Commercie\Currency\UsageInterface[]
   */
  protected $usages = [];

  /**
   * The UUID for this entity.
   *
   * @var string
   */
  public $uuid;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    if (isset($values['usages'])) {
      $usages_data = $values['usages'];
      $values['usages'] = [];
      foreach ($usages_data as $usage_data) {
        $usage = new Usage();
        $usage->setStart($usage_data['start'])
          ->setEnd($usage_data['end'])
          ->setCountryCode($usage_data['countryCode']);
        $values['usages'][] = $usage;
      }
    }
    parent::__construct($values, $entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label();
  }

  /**
   * {@inheritdoc}
   */
  public function setAlternativeSigns(array $signs) {
    $this->alternativeSigns = $signs;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlternativeSigns() {
    return $this->alternativeSigns;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrencyCode($code) {
    $this->currencyCode = $code;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrencyCode() {
    return $this->currencyCode;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrencyNumber($number) {
    $this->currencyNumber = $number;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrencyNumber() {
    return $this->currencyNumber;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRoundingStep($step) {
    $this->roundingStep = $step;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSign($sign) {
    $this->sign = $sign;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSign() {
    return $this->sign;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubunits($subunits) {
    $this->subunits = $subunits;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubunits() {
    return $this->subunits;
  }

  /**
   * {@inheritdoc}
   */
  public function setUsages(array $usages) {
    $this->usages = $usages;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUsages() {
    return $this->usages;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->currencyCode;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecimals() {
    $decimals = 0;
    if ($this->getSubunits() > 0) {
      $decimals = 1;
      while (pow(10, $decimals) < $this->getSubunits()) {
        $decimals++;
      }
    }

    return $decimals;
  }

  /**
   * {@inheritdoc}
   */
  function formatAmount($amount, $use_currency_precision = TRUE, $language_type = LanguageInterface::TYPE_CONTENT) {
    if ($use_currency_precision && $this->getSubunits()) {
      // Round the amount according the currency's configuration.
      $amount = bcmul(round(bcdiv($amount, $this->getRoundingStep(), 6)), $this->getRoundingStep(), 6);

      $decimal_mark_position = strpos($amount, '.');
      // The amount has no decimals yet, so add a decimal mark.
      if ($decimal_mark_position === FALSE) {
        $amount .= '.';
      }
      // Remove any existing trailing zeroes.
      $amount = rtrim($amount, '0');
      // Add the required number of trailing zeroes.
      $amount_decimals = strlen(substr($amount, $decimal_mark_position + 1));
      if ($amount_decimals < $this->getDecimals()) {
        $amount .= str_repeat('0', $this->getDecimals() - $amount_decimals);
      }
    }

    return $this->getCurrencyAmountFormatterManager()->getDefaultPlugin()->formatAmount($this, $amount, $language_type);
  }

  /**
   * Sets the entity type manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @return $this
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;

    return $this;
  }

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected function entityTypeManager() {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = parent::entityTypeManager();
    }

    return $this->entityTypeManager;
  }

  /**
   * Sets the currency amount formatter manager.
   *
   * @param \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface $currency_amount_formatter_manager
   *
   * @return $this
   */
  public function setCurrencyAmountFormatterManager(AmountFormatterManagerInterface $currency_amount_formatter_manager) {
    $this->currencyAmountFormatterManager = $currency_amount_formatter_manager;

    return $this;
  }

  /**
   * Gets the currency amount formatter manager.
   *
   * @return \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface
   */
  protected function getCurrencyAmountFormatterManager() {
    if (!$this->currencyAmountFormatterManager) {
      $this->currencyAmountFormatterManager = \Drupal::service('plugin.manager.currency.amount_formatter');
    }

    return $this->currencyAmountFormatterManager;
  }

  /**
   * {@inheritdoc}
   */
  function getRoundingStep() {
    if (is_numeric($this->roundingStep)) {
      return $this->roundingStep;
    }
    // If a rounding step was not set explicitly, the rounding step is equal
    // to one subunit.
    elseif (is_numeric($this->getSubunits())) {
      return $this->getSubunits() > 0 ? bcdiv(1, $this->getSubunits(), 6) : 1;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  function isObsolete($reference = NULL) {
    // Without usage information, we cannot know if the currency is obsolete.
    if (!$this->getUsages()) {
      return FALSE;
    }

    // Default to the current date and time.
    if (is_null($reference)) {
      $reference = time();
    }

    // Mark the currency obsolete if all usages have an end date that comes
    // before $reference.
    $obsolete = 0;
    foreach ($this->getUsages() as $usage) {
      if ($usage->getEnd()) {
        $to = strtotime($usage->getEnd());
        if ($to !== FALSE && $to < $reference) {
          $obsolete++;
        }
      }
    }
    return $obsolete == count($this->getUsages());
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $properties = parent::toArray();
    $properties['usages'] = [];
    foreach ($this->getUsages() as $usage) {
      $properties['usages'][] = array(
        'start' => $usage->getStart(),
        'end' => $usage->getEnd(),
        'countryCode' => $usage->getCountryCode(),
      );
    }
    return $properties;
  }

}
