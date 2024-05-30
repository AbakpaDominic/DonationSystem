<?php

namespace Drupal\commerce_paystack\PluginForm;

use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;
use Yabacon\Paystack\Exception\ApiException as PaystackApiException;
use Yabacon\Paystack;

class PaystackStandardForm extends BasePaymentOffsiteForm {
  
  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_paystack\Plugin\Commerce\PaymentGateway\PaystackStandardInterface $plugin */
    $plugin = $payment->getPaymentGateway()->getPlugin();
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $payment->getOrder();

    // Adds information about the billing profile.
    if ($billing_profile = $order->getBillingProfile()) {
      /** @var \Drupal\address\AddressInterface $address */
      $address = $billing_profile->get('address')->first();
      $fields = [
        [
          'display_name' => 'Billing First Name',
          'variable_name' => 'first_name',
          'value' => $address->getGivenName(),
        ],
        [
          'display_name' => 'Billing Surname',
          'variable_name' => 'last_name',
          'value' => $address->getFamilyName(),
        ]
      ];
    }
    
    // Get total order price.
    $amount = $payment->getAmount();
    
    $transactionData = [
      'reference' => $order->uuid(),
      'amount' => $amount->getNumber() * 100, // Convert to kobo.
      'email' => $order->getEmail(),
      'callback_url' => $form['#return_url'],
      'metadata' => [
        'cancel_action' => $form['#cancel_url'],
      ],
    ];
    if (isset($fields)) {
      $transactionData['metadata']['custom_fields'] = $fields;
    }
    
    // Initialize a transaction.
    $paystack = new Paystack($plugin->getSecretKey());
    try {
      $responseObj = $paystack->transaction->initialize($transactionData);
    }
    catch (PaystackApiException $e) {
      throw new PaymentGatewayException($e->getMessage());
    }
    
    $redirectUrl = $responseObj->data->authorization_url;
    $order->setData('paystack_standard', [
      'reference' => $responseObj->data->reference,
      'access_code' => $responseObj->data->access_code,
      'authorization_url' => $redirectUrl,
    ]);
    $order->save();
    
    $data = [
      'return' => $form['#return_url'],
      'cancel' => $form['#cancel_url'],
      'total' => $payment->getAmount()->getNumber(),
    ];

    $redirectMethod = BasePaymentOffsiteForm::REDIRECT_GET;
    $form = $this->buildRedirectForm($form, $form_state, $redirectUrl, $data, $redirectMethod);

    return $form;
  }
  
}
