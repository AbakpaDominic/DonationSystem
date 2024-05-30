<?php

namespace Drupal\currency\Entity\Currency;

use Commercie\Currency\InputInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a currency add/edit form.
 */
class CurrencyForm extends EntityForm {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * The Currency input parser.
   *
   * @var \Commercie\Currency\InputInterface
   */
  protected $inputParser;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $link_generator
   *   The link generator.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   *   The currency storage.
   * @param \Commercie\Currency\InputInterface $input_parser
   *   The Currency input parser.
   */
  public function __construct(TranslationInterface $string_translation, LinkGeneratorInterface $link_generator, EntityStorageInterface $currency_storage, InputInterface $input_parser) {
    $this->currencyStorage = $currency_storage;
    $this->inputParser = $input_parser;
    $this->linkGenerator = $link_generator;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static($container->get('string_translation'), $container->get('link_generator'), $entity_type_manager->getStorage('currency'), $container->get('currency.input'));
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = $entity;
    $values = $form_state->getValues();
    $currency->setCurrencyCode($values['currency_code']);
    $currency->setCurrencyNumber($values['currency_number']);
    $currency->setLabel($values['label']);
    $currency->setSign($values['sign']);
    $currency->setSubunits($values['subunits']);
    $currency->setRoundingStep($values['rounding_step']);
    $currency->setStatus($values['status']);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = $this->getEntity();

    $form['currency_code'] = array(
      '#default_value' => $currency->getCurrencyCode(),
      '#disabled' => !$currency->isNew(),
      '#element_validate' => array(array($this, 'validateCurrencyCode')),
      '#maxlength' => 3,
      '#pattern' => '[a-zA-Z]{3}',
      '#placeholder' => 'XXX',
      '#required' => TRUE,
      '#size' => 3,
      '#title' => $this->t('ISO 4217 code'),
      '#type' => 'textfield',
    );

    $form['currency_number'] = array(
      '#default_value' => $currency->getCurrencyNumber(),
      '#element_validate' => array(array($this, 'validateCurrencyNumber')),
      '#maxlength' => 3,
      '#pattern' => '[\d]{3}',
      '#placeholder' => '999',
      '#size' => 3,
      '#title' => $this->t('ISO 4217 number'),
      '#type' => 'textfield',
    );

    $form['status'] = array(
      '#default_value' => $currency->status(),
      '#title' => $this->t('Enabled'),
      '#type' => 'checkbox',
    );

    $form['label'] = array(
      '#default_value' => $currency->label(),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
    );

    $form['sign'] = array(
      '#currency_code' => $currency->getCurrencyCode(),
      '#default_value' => $currency->getSign(),
      '#title' => $this->t('Sign'),
      '#type' => 'currency_sign',
    );

    $form['subunits'] = array(
      '#default_value' => $currency->getSubunits(),
      '#min' => 0,
      '#required' => TRUE,
      '#title' => $this->t('Number of subunits'),
      '#type' => 'number',
    );

    $form['rounding_step'] = array(
      '#default_value' => $currency->getRoundingStep(),
      '#element_validate' => array(array($this, 'validateRoundingStep')),
      '#required' => TRUE,
      '#title' => $this->t('Rounding step'),
      '#type' => 'textfield',
    );

    return parent::form($form, $form_state, $currency);
  }

  /**
   * {@inheritdoc}.
   */
  public function save(array $form, FormStateInterface $form_state) {
    $currency = $this->getEntity($form_state);
    $currency->save();
    $this->messenger()->addMessage($this->t('The currency %label has been saved.', array(
      '%label' => $currency->label(),
    )));
    $form_state->setRedirect('entity.currency.collection');
  }

  /**
   * Implements #element_validate for the currency code element.
   */
  public function validateCurrencyCode(array $element, FormStateInterface $form_state, array $form) {
    $currency = $this->getEntity();
    $currency_code = $element['#value'];
    if (!preg_match('/^[a-z]{3}$/i', $currency_code)) {
      $form_state->setError($element, $this->t('The currency code must be three letters.'));
    }
    elseif ($currency->isNew()) {
      $loaded_currency = $this->currencyStorage->load($currency_code);
      if ($loaded_currency) {
        $form_state->setError($element, $this->t('The currency code is already in use by @link.', array(
          '@link' => $this->linkGenerator->generate($loaded_currency->label(), $loaded_currency->toUrl('edit-form')),
        )));
      }
    }
  }

  /**
   * Implements #element_validate for the currency number element.
   */
  public function validateCurrencyNumber(array $element, FormStateInterface $form_state, array $form) {
    $currency = $this->getEntity();
    $currency_number = $element['#value'];
    if ($currency_number && !preg_match('/^\d{3}$/i', $currency_number)) {
      $form_state->setError($element, $this->t('The currency number must be three digits.'));
    }
    elseif ($currency->isNew()) {
      $loaded_currencies = $this->currencyStorage->loadByProperties(array(
        'currencyNumber' => $currency_number,
      ));
      if ($loaded_currencies) {
        $loaded_currency = reset($loaded_currencies);
        $form_state->setError($element, $this->t('The currency number is already in use by @link.', array(
          '@link' => $this->linkGenerator->generate($loaded_currency->label(), $loaded_currency->toUrl('edit-form')),
        )));
      }
    }
  }

  /**
   * Implements #element_validate for the rounding step element.
   */
  public function validateRoundingStep(array $element, FormStateInterface $form_state, array $form) {
    $rounding_step = $this->inputParser->parseAmount($element['#value']);
    if ($rounding_step === FALSE) {
      $form_state->setError($element, $this->t('The rounding step is not numeric.'));
    }
    $form_state->setValueForElement($element, $rounding_step);
  }
}
