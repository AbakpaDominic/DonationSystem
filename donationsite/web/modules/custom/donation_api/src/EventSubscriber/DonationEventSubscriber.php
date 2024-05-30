<?php

namespace Drupal\donation_api\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\donation_api\Utility\EmailUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DonationEventSubscriber.
 *
 * Subscribes to Commerce Order events.
 */
class DonationEventSubscriber implements EventSubscriberInterface {
  protected $mailManager;
  protected $languageManager;
  protected $configFactory;
  protected $logger;

  /**
   * Constructs a new DonationEventSubscriber.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory service.
   */
  public function __construct(MailManagerInterface $mail_manager, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('donation_api');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Subscribe to the order paid event.
    return [
      OrderEvents::ORDER_PAID => 'onOrderPaid',
    ];
  }

  /**
   * Handles the order paid event.
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The order event.
   */
  public function onOrderPaid(OrderEvent $event) {
    $order = $event->getOrder();
    $email = $order->getEmail();
    $order_data = $order->toArray();

    // Log the order ID and email for debugging purposes.
    $this->logger->info('Processing order paid event for order ID: @order_id and email: @customer_email', [
      '@order_id' => $order->id(),
      '@customer_email' => $email,
    ]);

    // Send acknowledgment email.
    try {
      EmailUtility::sendAcknowledgmentEmail($email, $order_data);
      $this->logger->info('Acknowledgment email sent for order ID: @order_id', ['@order_id' => $order->id()]);
    } catch (\Exception $e) {
      $this->logger->error('Failed to send acknowledgment email for order ID: @order_id. Error: @error', [
        '@order_id' => $order->id(),
        '@error' => $e->getMessage(),
      ]);
    }
  }
}
