<?php

namespace Drupal\donation_api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\donation_api\Utility\EmailUtility;

/**
 * Class WebhookController.
 */
class WebhookController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new WebhookController object.
   */
  public function __construct(Connection $database, LoggerInterface $logger) {
    $this->database = $database;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('logger.factory')->get('donation_api')
    );
  }

  /**
   * Handles webhook requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response object.
   */
  public function handleWebhook(Request $request) {
    $payload = $request->getContent();

    // Ensure the payload is not empty.
    if (!empty($payload)) {
      try {
        // Decode the JSON payload.
        $data = json_decode($payload, TRUE);

        // Check if the data is valid.
        if (is_array($data) && isset($data['event'])) {
          // Process the webhook event based on its type.
          switch ($data['event']) {
            case 'subscription.create':
              $this->handleSubscriptionCreate($data);
              break;

            case 'subscription.charge.success':
              $this->handleSubscriptionChargeSuccess($data);
              break;

            case 'subscription.charge.failed':
              $this->handleSubscriptionChargeFailed($data);
              break;

            default:
              $this->logger->warning('Unhandled webhook event: @event', ['@event' => $data['event']]);
              break;
          }
        } else {
          $this->logger->error('Invalid webhook payload: @payload', ['@payload' => $payload]);
        }
      } catch (\Exception $e) {
        $this->logger->error('Error processing webhook: @error', ['@error' => $e->getMessage()]);
      }
    } else {
      $this->logger->error('Empty webhook payload received.');
    }

    // Return a response indicating successful processing.
    return new JsonResponse(['status' => 'success'], 200);
  }

  /**
   * Handles subscription creation events.
   *
   * @param array $data
   *   The webhook data.
   */
  protected function handleSubscriptionCreate(array $data) {
    // Implement the logic for handling subscription creation.
    $this->logger->info('Subscription created: @data', ['@data' => json_encode($data)]);
  }

  /**
   * Handles successful subscription charge events.
   *
   * @param array $data
   *   The webhook data.
   */
  protected function handleSubscriptionChargeSuccess(array $data) {
    // Implement the logic for handling successful subscription charges.
    $this->logger->info('Subscription charge succeeded: @data', ['@data' => json_encode($data)]);

    // Assuming $data contains 'email' and 'amount'.
    if (isset($data['data']['customer']['email']) && isset($data['data']['amount'])) {
      $email = $data['data']['customer']['email'];
      $amount = $data['data']['amount'];

      // Example order data, replace with actual data.
      $order_data = [
        'donor_details' => ['name' => 'Donor Name'],
        'amount' => $amount,
        'frequency' => 'monthly',
      ];

      // Send acknowledgment email.
      try {
        EmailUtility::sendAcknowledgmentEmail($email, $order_data);
        $this->logger->info('Acknowledgment email sent for successful charge.');
      } catch (\Exception $e) {
        $this->logger->error('Failed to send acknowledgment email: @error', ['@error' => $e->getMessage()]);
      }
    } else {
      $this->logger->error('Email or amount not found in webhook data: @data', ['@data' => json_encode($data)]);
    }
  }

  /**
   * Handles failed subscription charge events.
   *
   * @param array $data
   *   The webhook data.
   */
  protected function handleSubscriptionChargeFailed(array $data) {
    // Implement the logic for handling failed subscription charges.
    $this->logger->warning('Subscription charge failed: @data', ['@data' => json_encode($data)]);
  }
}
