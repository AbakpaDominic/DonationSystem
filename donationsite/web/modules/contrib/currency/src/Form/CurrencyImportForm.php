<?php

namespace Drupal\currency\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Form;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\currency\ConfigImporterInterface;
use Drupal\currency\FormHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the currency import form.
 */
class CurrencyImportForm extends FormBase implements ContainerInjectionInterface {

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
    return 'currency_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $currencies = $this->configImporter->getImportableCurrencies();
    if (empty($currencies)) {
      $form['message'] = [
        '#markup' => $this->t('All currencies have been imported already.'),
      ];
    }
    else {
      $form['currency_code'] = [
        '#options' => $this->formHelper->getCurrencyOptions($currencies),
        '#required' => TRUE,
        '#title' => $this->t('Currency'),
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
    $currency = $this->configImporter->importCurrency($form_state->getValues()['currency_code']);
    $this->messenger()->addMessage($this->t('The %label has been imported.', [
      '%label' => $currency->label(),
    ]));

    if ($form_state->getTriggeringElement()['#name'] == 'import_edit') {
      $form_state->setRedirectUrl($currency->toUrl('edit-form'));
    }
    else {
      $form_state->setRedirectUrl(new Url('entity.currency.collection'));
    }
  }

}
