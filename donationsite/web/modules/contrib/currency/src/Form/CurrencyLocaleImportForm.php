<?php

namespace Drupal\currency\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\currency\ConfigImporterInterface;
use Drupal\currency\FormHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the currency locale import form.
 */
class CurrencyLocaleImportForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The Currency config importer.
   *
   * @var \Drupal\currency\ConfigImporterInterface
   */
  protected $configImporter;

  /**
   * The Currency form helper.
   *
   * @var \Drupal\currency\FormHelperInterface
   */
  protected $formHelper;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\currency\ConfigImporterInterface
   *   The Currency config importer.
   * @param \Drupal\currency\FormHelperInterface
   *   The Currency form helper.
   */
  public function __construct(TranslationInterface $string_translation, ConfigImporterInterface $config_importer, FormHelperInterface $form_helper) {
    $this->configImporter = $config_importer;
    $this->formHelper = $form_helper;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('string_translation'), $container->get('currency.config_importer'), $container->get('currency.form_helper'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'currency_locale_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $currency_locales = $this->configImporter->getImportableCurrencyLocales();
    if (empty($currency_locales)) {
      $form['message'] = [
        '#markup' => $this->t('All currency locales have been imported already.'),
      ];
    }
    else {
      $form['locale'] = [
        '#options' => $this->formHelper->getCurrencyLocaleOptions($currency_locales),
        '#required' => TRUE,
        '#title' => $this->t('Currency locale'),
        '#type' => 'select',
      ];
      $form['actions'] = [
        '#type' => 'actions',
      ];
      $form['actions']['import'] = [
        '#dropbutton' => 'submit',
        '#name' => 'import',
        '#type' => 'submit',
        '#value' => $this->t('Import'),
      ];
      $form['actions']['import_edit'] = [
        '#dropbutton' => 'submit',
        '#name' => 'import_edit',
        '#type' => 'submit',
        '#value' => $this->t('Import and edit'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $currency_locale = $this->configImporter->importCurrencyLocale($form_state->getValues()['locale']);
    $this->messenger()->addMessage($this->t('The %label has been imported.', [
      '%label' => $currency_locale->label(),
    ]));

    if ($form_state->getTriggeringElement()['#name'] == 'import_edit') {
      $form_state->setRedirectUrl($currency_locale->toUrl('edit-form'));
    }
    else {
      $form_state->setRedirectUrl(new Url('entity.currency_locale.collection'));
    }
  }

}
