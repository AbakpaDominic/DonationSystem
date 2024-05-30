<?php

namespace Drupal\currency\Entity\CurrencyLocale;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a currency_locale add/edit form.
 */
class CurrencyLocaleForm extends EntityForm {

  /**
   * The country manager.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * The currency locale storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyLocaleStorage;

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
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_locale_storage
   *   The currency storage.
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   The country manager.
   */
  public function __construct(TranslationInterface $string_translation, LinkGeneratorInterface $link_generator, EntityStorageInterface $currency_locale_storage, CountryManagerInterface $country_manager) {
    $this->countryManager = $country_manager;
    $this->currencyLocaleStorage = $currency_locale_storage;
    $this->linkGenerator = $link_generator;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static($container->get('string_translation'), $container->get('link_generator'), $entity_type_manager->getStorage('currency_locale'), $container->get('country_manager'));
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\currency\Entity\CurrencyLocaleInterface $currency_locale */
    $currency_locale = $entity;
    $values = $form_state->getValues();
    $currency_locale->setLocale($values['language_code'], $values['country_code']);
    $currency_locale->setPattern($values['pattern']);
    $currency_locale->setDecimalSeparator($values['decimal_separator']);
    $currency_locale->setGroupingSeparator($values['grouping_separator']);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\currency\Entity\CurrencyLocaleInterface $currency_locale */
    $currency_locale = $this->getEntity();

    $options = array();
    foreach (LanguageManager::getStandardLanguageList() as $language_code => $language_names) {
      $options[$language_code] = $language_names[0];
    }
    natcasesort($options);
    $form['language_code'] = array(
      '#default_value' => $currency_locale->getLanguageCode(),
      '#empty_value' => '',
      '#options' => $options,
      '#required' => TRUE,
      '#title' => $this->t('Language'),
      '#type' => 'select',
    );
    $form['country_code'] = array(
      '#default_value' => $currency_locale->getCountryCode(),
      '#empty_value' => '',
      '#options' => $this->countryManager->getList(),
      '#required' => TRUE,
      '#title' => $this->t('Country'),
      '#type' => 'select',
    );
    $form['formatting'] = array(
      '#open' => TRUE,
      '#title' => $this->t('Formatting'),
      '#type' => 'details',
    );
    $form['formatting']['decimal_separator'] = array(
      '#default_value' => $currency_locale->getDecimalSeparator(),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#size' => 3,
      '#title' => $this->t('Decimal separator'),
      '#type' => 'textfield',
    );
    $form['formatting']['grouping_separator'] = array(
      '#default_value' => $currency_locale->getGroupingSeparator(),
      '#maxlength' => 255,
      '#size' => 3,
      '#title' => $this->t('Group separator'),
      '#type' => 'textfield',
    );
    $form['formatting']['pattern'] = array(
      '#default_value' => $currency_locale->getPattern(),
      '#description' => $this->t('A Unicode <abbr title="Common Locale Data Repository">CLDR</abbr> <a href="http://cldr.unicode.org/translation/number-patterns">currency number pattern</a>.'),
      '#maxlength' => 255,
      '#title' => $this->t('Pattern'),
      '#type' => 'textfield',
    );

    return parent::form($form, $form_state, $currency_locale);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $currency_locale = $this->getEntity();
    if ($currency_locale->isNew()) {
      $values = $form_state->getValues();
      $locale = strtolower($values['language_code']) . '_' . strtoupper($values['country_code']);
      $loaded_currency_locale = $this->currencyLocaleStorage->load($locale);
      if ($loaded_currency_locale) {
        $form_state->setError($form['locale'], $this->t('A pattern for this locale already exists.'));
      }
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function save(array $form, FormStateInterface $form_state) {
    $currency_locale = $this->getEntity($form_state);
    $currency_locale->save();
    $this->messenger()->addMessage($this->t('The currency locale %label has been saved.', array(
      '%label' => $currency_locale->label(),
    )));
    $form_state->setRedirect('entity.currency_locale.collection');
  }

}
