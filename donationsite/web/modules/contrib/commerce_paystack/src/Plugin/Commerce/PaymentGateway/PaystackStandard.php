<?php

namespace Drupal\commerce_paystack\Plugin\Commerce\PaymentGateway;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Symfony\Component\HttpFoundation\Request;
use Yabacon\Paystack;
use Yabacon\Paystack\Exception\ApiException as PaystackApiException;

/**
 * Provides the Paystack Standard Off-site payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "paystack_standard",
 *   label = "Paystack Standard (Off-site)",
 *   display_label = "Paystack",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_paystack\PluginForm\PaystackStandardForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "mastercard", "visa",
 *   },
 *   requires_billing_information = FALSE,
 * )
 */
class PaystackStandard extends OffsitePaymentGatewayBase implements PaystackStandardInterface {

  /**
   * {@inheritdoc}
   */
  public function getSecretKey() {
    return $this->configuration['secret_key'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'secret_key' => '',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret Key'),
      '#default_value' => $this->getSecretKey(),
      '#required' => TRUE,
    ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    // Validate the secret key.
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $secre_key = $values['secret_key'];
      if (!is_string($secre_key) || !(substr($secre_key, 0, 3) === 'sk_')) {
        $form_state->setError($form['secret_key'], $this->t('A Valid Paystack Secret Key must start with \'sk_\'.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['secret_key'] = $values['secret_key'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    $order_id = $order->id();
    $trxref = $request->query->get('trxref');
    if (empty($trxref)) {
      $this->messenger()->addError($this->t('Could not complete your payment with Paystack. Please try again or contact us if the problem persists.'));
      throw new PaymentGatewayException("Could not find Transaction Reference in return request. Order ID - $order_id.");
    }
    $verify_transaction = $this->verifyTransaction($trxref);
    // Checks if the response contains an error.
    if (!$verify_transaction->status) {
      $this->messenger()->addError($this->t('Could not complete your payment with Paystack. Please try again or contact us if the problem persists.'));
      throw new PaymentGatewayException("Payment was not successful for order ID - $order_id.");
    }
    $remote_state = $verify_transaction->data->status;
    // Set the Payer ID used to finalize payment.
    $orderData = $order->getData('paystack_standard');
    $orderData['payer_id'] = $verify_transaction->data->customer->id;
    $order->setData('paystack_standard', $orderData);
    // Set commerce payment data.
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $payment = $payment_storage->create([
      'state' => 'authorization',
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order_id,
      'remote_id' => $verify_transaction->data->id,
      'remote_state' => $remote_state,
    ]);
    // Update payment status.
    $status_mapping = $this->getStatusMapping();
    if (isset($status_mapping[$remote_state])) {
      $payment->setState($status_mapping[$remote_state]);
    }
    $payment->save();
  }

  /**
   * {@inheritdoc}
   */
  public function onCancel(OrderInterface $order, Request $request) {
    parent::onCancel($order, $request);
    $this->messenger()->addMessage($this->t('Payment canceled.'));
  }

  /**
   * {@inheritdoc}
   */
  public function verifyTransaction($reference) {
    try {
      $paystack = new Paystack($this->getSecretKey());
      $response_obj = $paystack->transaction->verify(['reference' => $reference]);
      return $response_obj;
    }
    catch (PaystackApiException $e) {
      $logger = \Drupal::logger('commerce_paystack');
      $logger->error('An error occurred while verifying transaction: ' . $reference . '. Error: ' . $e->getMessage());
      return [
        'status' => FALSE,
        'data' => $e->getMessage(),
      ];
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function getStatusMapping($status = NULL) {
    $mapping = [
      'success' => 'completed',
      'abandoned' => 'authorization_voided',
      'failed' => 'refunded',
    ];
    // If a status was passed, return its corresponding payment state.
    if (isset($status) && isset($mapping[$status])) {
      return $mapping[$status];
    }
    return $mapping;
  }
  
}
