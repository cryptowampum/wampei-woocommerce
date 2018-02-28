# WooCommerce Bitcoin payment api for Wampei Register #
**Contributors:** 
**Tags:** woocommerce, bitcoin, payment, noncustodial, non-custodial , wampei, Wampei Register
**Requires at least:** 4.0  
**Tested up to:** 4.9  
**Stable tag:** 1.1.0
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html


Allows stores to accept Bitcoin as a payment method in WooCommerce. Interfaces with through Wampei Register© merchant wallet software.  [See](http://wampei.com/blog/woocommerce/plugin/ecommerce/using_wampum_woocommerce_plugin.html)

## Description ##

This plugin adds a Bitcoin payment method to your store.  It requires that you have access to a Wampei Register© instance or white-labeled service that supports Wampei's API.  The plugin automatically converts the price from the currency you use to Bitcoin, but only supports ONE CURRENCY.  The one that your Wampei Register© instance is configured to use.  Using Euros or Yen or anything is ok, but the setting will apply to all payments through your store.

The beautiful thing about this plugin is that it integrates so nicely into your existing workflow.  When the payment page is displayed, the system will send an email to the customer to make sure they can pay later if they don't currently have enough bitcoin available in his/her wallet.  You can also provide a link to the customer to purchase bitcoin from your favorite exchange or Bitcoin ATM.

A "cron" job checks the blockchain at 20-minute increments to see the status of the payment and sends the receipt to the customer and order notice to the store owner, assuming this is your standard workflow.

You can add the Bitcoin payment method to your store
Settings configured in the WooCommerce Checkout Tab:
Title: Title of the payment method, typically "Bitcoin."
Customer Message: What you want your store to say as a thank you, e.g., "Thank you for purchasing with Bitcoin with Wampei Register©."
Wampei URL: URL of your Wampei Register© instance
Wampei Username: User name of the remote API user
Wampei Password: Password of the remote API user

Note that this plugin has only been tested with WooCommerce 4.0 and later.


## Frequently Asked Questions ##


### What is needed to use this plugin? ###

[WooCommerce](http://wordpress.org/plugins/woocommerce/) 4.0 or later.
[Wampei Register© Instance](http://Wampei.com) either a self-hosted or SaaS instance of Wampei Register© software.  

### How can I get Wampei Register© and start accepting Bitcoin? ###

At Wampei we believe that eCommerce tech should be available to all. We help our customers accept crypto payments with or without the use of a bank. Our software does give you the total control over your funds at all times. You don't have to have a bank account and neither do your customers to accept Bitcoin with Wampei Register©.
[Sign Up](http://Wampei.com/contact/index.html)

### Need help or want to make a suggestion? ###

Contact us at info@cryptowampum.com

### Are non-US currencies supported? ###
Currently we only support one currency, the one you have configured Wampei Register© to use.
## Screenshots ##

### 1. Settings page. ###
You can configure the plugin via the WooCommerce settings pane.


![Settings pane](http://wampei.com/assets/files/gateway-wampum-bitcoin/PluginSettings.png "Settings Pane")


### 2. Plugin in action on payment page. ###
You can see here that the Bitcoin payment method is here.  You can name it in the settings pane.

![Plugin in action.](http://wampei.com/assets/files/gateway-wampum-bitcoin/PaymentMethodsPage.png)


### 3. Plugin in action on payment method checkout page. ###
Note that the payer can use the QR code or copy the payment request to the clipboard.

![Payment Page](http://wampei.com/assets/files/gateway-wampum-bitcoin/PaymentPage.png)

## Changelog ##

### 1.0.3 ###

- Added support for WooCommerce 4.8.
- Added ability to Use Euros
## Upgrade Notice ##
