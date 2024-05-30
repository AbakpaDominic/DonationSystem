README file for Commerce Paystack

CONTENTS OF THIS FILE
---------------------
* Introduction
* Requirements
* Installation
* Configuration
* How it works
* Troubleshooting
* Maintainers

INTRODUCTION
------------
This project integrates Pastask payment Gateway into the Drupal Commerce
payment and checkout systems. It currently supports standard workflow from Paystack.
https://paystack.com/developers
* For a full description of the module, visit the project page:
  https://www.drupal.org/project/commerce_paystack
* To submit bug reports and feature suggestions, or to track changes:
  https://www.drupal.org/project/issues/commerce_paystack


REQUIREMENTS
------------
This module requires the following modules:
* Submodules of Drupal Commerce package (https://drupal.org/project/commerce)
  - Commerce core
  - Commerce Payment (and its dependencies)
* [Yabacon Paystack PHP Library](https://github.com/yabacon/paystack-php)
* [Paystack account](https://dashboard.paystack.com/#/signup)


INSTALLATION
------------
* Install as you would normally install a contributed drupal module.
  See: Installing modules (Drupal 8) [documentation page](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules) for further information.


CONFIGURATION
-------------
  * Permissions: There are no specific permissions for this module. The
    Payments permissions are to be used for configurations.
  * Enable the Paystack payment methods on the [Payment gateways page](/admin/commerce/config/payment-gateways).
  * Avaliable payments flow:
    * Paystack Standard (Off-site). Configure Paystack Standard"payment.
        * Transaction mode: either if a test/development store or a production one.
          * Available options: "Live" and "Test";
        * Secret Key: Merchant secret key for the payment gateway.
    * TO DO.


HOW IT WORKS
------------

  * General considerations:
    * Shop owner must have an [Paystack account](https://dashboard.paystack.com/#/signup)
    * Customer should have an Paystack account or he will be asked to [create one.](https://dashboard.paystack.com/#/signup)
  * Customer/Checkout workflows:
    * This is an Off-Site payment method
    * Redirect customer from checkout to the payment service and back.
      * Customer redirected to Paystack where an Paystack account is needed
      * Sign In or Create Account;
      * After the payment confirmation on Paystack side the Customer will be
        redirected  back to the store to complete the order checkout.


MAINTAINERS
-----------
This project has been developed by [Ivan Trokhanenko](https://www.drupal.org/u/i-trokhanenko).
