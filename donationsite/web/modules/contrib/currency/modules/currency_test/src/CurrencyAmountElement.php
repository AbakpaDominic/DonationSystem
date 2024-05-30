<?php

namespace Drupal\currency_test;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to test the currency_amount element.
 */
class CurrencyAmountElement implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'currency_test_currency_amount_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $minimum_amount = NULL, $maximum_amount = NULL, $currency_code = NULL) {
    // Nest the element to make sure that works.
    $form['container'] = array(
      '#tree' => TRUE,
    );
    $form['container']['amount'] = array(
      '#limit_currency_codes' => $currency_code ? array($currency_code) : array(),
      '#type' => 'currency_amount',
      '#title' => t('Foo amount'),
      '#minimum_amount' => $minimum_amount,
      '#maximum_amount' => $maximum_amount,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    \Drupal::state()->set('currency_test_currency_amount_element', $values['container']['amount']);
  }
}
