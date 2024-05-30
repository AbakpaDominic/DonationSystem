<?php

namespace Drupal\donation_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Drupal\donation_api\Utility\EmailUtility;
use Drupal\commerce_order\Entity\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;

class DonationController extends ControllerBase {
  private $paystackSecretKey;
  protected $entityTypeManager;
  protected $logger;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->paystackSecretKey = getenv('PAYSTACK_SECRET_KEY');
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.factory')->get('donation_api')
    );
  }

  public function handleDonations(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    // Log the received data for debugging
    $this->logger->debug('Received data: ' . print_r($data, true));

    // Validate the received data
    if (empty($data['amount']) || empty($data['donor_details']['email'])) {
      return new JsonResponse(['message' => 'Invalid input'], 400);
    }

    // Log the Paystack secret key for debugging
    $this->logger->debug('Paystack Secret Key: ' . $this->paystackSecretKey);

    // Process payment
    $payment_successful = $this->processPayment($data);

    if ($payment_successful) {
      // Create order and update status
      $order = $this->createOrder($data);

      // Handle recurring payments
      if ($data['frequency'] !== 'one_time') {
        $this->createSubscription($data);
      }

      // Send acknowledgment email
      EmailUtility::sendAcknowledgmentEmail($data['donor_details']['email'], $data);

      return new JsonResponse(['message' => 'Donation processed successfully']);
    } else {
      return new JsonResponse(['message' => 'Payment failed'], 400);
    }
  }

  private function processPayment($data) {
    $client = new Client();
    try {
      $response = $client->post('https://api.paystack.co/transaction/initialize', [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->paystackSecretKey,
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'email' => $data['donor_details']['email'],
          'amount' => $data['amount'] * 100, // Convert to kobo
        ],
      ]);

      $body = json_decode($response->getBody(), true);

      // Log the response for debugging
      $this->logger->debug('Paystack Response: ' . print_r($body, true));

      if ($body['status']) {
        // Redirect user to authorization_url to complete payment
        return true; // Payment initialized successfully
      } else {
        return false;
      }
    } catch (\Exception $e) {
      // Log the error
      $this->logger->error('Payment initialization failed: ' . $e->getMessage());
      return false;
    }
  }

  private function createOrder($data) {
    $order_storage = $this->entityTypeManager->getStorage('commerce_order');
    $order = $order_storage->create([
      'type' => 'donation',
      'mail' => $data['donor_details']['email'],
      'state' => 'completed',
      'order_number' => $this->generateOrderNumber(),
      'store_id' => 1,
      'uid' => 0,
      'order_items' => [],
    ]);

    $order->save();
    return $order;
  }

  private function createSubscription($data) {
    $client = new Client();
    try {
      $response = $client->post('https://api.paystack.co/subscription', [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->paystackSecretKey,
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'customer' => $data['donor_details']['email'],
          'plan' => $this->getPlanCode($data['frequency']),
        ],
      ]);

      $body = json_decode($response->getBody(), true);
      // Log the response for debugging
      $this->logger->debug('Paystack Subscription Response: ' . print_r($body, true));

      if (!$body['status']) {
        throw new \Exception('Failed to create subscription: ' . $body['message']);
      }
    } catch (\Exception $e) {
      $this->logger->error('Subscription creation failed: ' . $e->getMessage());
      throw $e;
    }
  }

  private function getPlanCode($frequency) {
    switch ($frequency) {
      case 'monthly':
        return 'plan_code_for_monthly';
      case 'quarterly':
        return 'plan_code_for_quarterly';
      case 'yearly':
        return 'plan_code_for_yearly';
      default:
        throw new \Exception('Invalid frequency');
    }
  }

  private function generateOrderNumber() {
    // Generate a unique order number
    return 'DON-' . strtoupper(uniqid());
  }
}

