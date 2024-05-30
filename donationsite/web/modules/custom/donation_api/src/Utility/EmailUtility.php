<?php

namespace Drupal\donation_api\Utility;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmailUtility.
 *
 * Utility class for sending emails.
 */
class EmailUtility implements ContainerInjectionInterface {
  protected static $mailManager;
  protected static $languageManager;

  /**
   * Constructs a new EmailUtility.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(MailManagerInterface $mail_manager, LanguageManagerInterface $language_manager) {
    self::$mailManager = $mail_manager;
    self::$languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('language_manager')
    );
  }

  /**
   * Sends an acknowledgment email.
   *
   * @param string $email
   *   The recipient email address.
   * @param array $data
   *   The data to be included in the email.
   *
   * @throws \Exception
   *   Thrown when email sending fails.
   */
  public static function sendAcknowledgmentEmail($email, array $data) {
    $module = 'donation_api';
    $key = 'donation_receipt';
    $to = $email;
    $params = self::buildEmailParams($data);
    $langcode = self::$languageManager->getDefaultLanguage()->getId();
    $send = true;

    $result = self::$mailManager->mail($module, $key, $to, $langcode, $params, null, $send);

    if ($result['result'] !== true) {
      throw new \Exception('There was a problem sending your email.');
    }
  }

  /**
   * Builds the email parameters.
   *
   * @param array $data
   *   The data to be included in the email.
   *
   * @return array
   *   The email parameters.
   */
  protected static function buildEmailParams(array $data) {
    $name = $data['donor_details']['name'];
    $amount = number_format($data['amount'] / 100, 2); // Convert amount from kobo to Naira
    $date = date('F j, Y, g:i a');
    $frequency = isset($data['frequency']) && $data['frequency'] !== 'one_time' ? ucfirst($data['frequency']) : 'One-time';
    $next_donation_date = '';

    if ($frequency !== 'One-time') {
      switch ($frequency) {
        case 'Monthly':
          $next_donation_date = date('F j, Y', strtotime('+1 month'));
          break;
        case 'Quarterly':
          $next_donation_date = date('F j, Y', strtotime('+3 months'));
          break;
        case 'Yearly':
          $next_donation_date = date('F j, Y', strtotime('+1 year'));
          break;
      }
    }

    $message = "
      <p>Hello $name,</p>
      <p>Thank you for your generous donation to Kindlegate Foundation. Your support helps us continue our mission and make a difference in the community.</p>
      <p><strong>Donation Details:</strong></p>
      <ul>
        <li>Amount: ₦$amount</li>
        <li>Date: $date</li>
        <li>Frequency: $frequency</li>
    ";

    if ($frequency !== 'One-time') {
      $message .= "<li>Next Donation Date: $next_donation_date</li>";
    }

    $message .= "
      </ul>
      <p>We are incredibly grateful for your support. Together, we can achieve more and create lasting change.</p>
      <p>Sincerely,<br>Kindlegate Foundation</p>
    ";

    return [
      'subject' => 'Kindlegate Foundation Donation Receipt',
      'body' => $message,
    ];
  }
}
