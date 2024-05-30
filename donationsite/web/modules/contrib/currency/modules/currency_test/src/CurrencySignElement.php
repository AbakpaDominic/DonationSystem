<?php

namespace Drupal\currency_test;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to test the currency_sign element.
 */
class CurrencySignElement implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'currency_test_currency_sign_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $currency_code = NULL, $currency_sign = NULL) {
    // Nest the element to make sure that works.
    $form['container'] = array(
      '#tree' => TRUE,
    );
    $form['container']['sign'] = array(
      '#currency_code' => $currency_code ? $currency_code : FALSE,
      '#default_value' => $currency_sign,
      '#type' => 'currency_sign',
      '#title' => t('Foo sign'),
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
    \Drupal::state()->set('currency_test_currency_sign_element', $values['container']['sign']);
  }
}
