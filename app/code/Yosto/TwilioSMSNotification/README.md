## Twilio sms notification
As a busy online store receiving a bucket load of orders by the hour, you might not have much time to think about
how you communicate with your customers once they have ordered from you!
For many businesses, email is the obvious option; it is cheap, everyone else does it and all of your customers have
an email address. But that is exactly the problem, because everyone else uses email, your communications get buried
meaning that you have no customer service!
According to MailChimp, only 17.35% of eCommerce emails get opened. Ultimately, this means that, if you currently
use email for order updates then these communications are potentially reaching less than 1 in 5 customers.
Twilio SMS Notification is a tool for you do that. With full configuration, easy to customize SMS, very fast
and trust because we use Twilio services, it will become powerful option to reach your customers.

###Benefits
- Notify customer instantly from Store owner when the order is processed by SMS.
- Store owner get SMS notification when have new order from customer.
With this extension, the order will be update, process faster because we use a direct way
for the communication between customer and store owner.

###Features
- SMS notify to Store owner when customer place order
- SMS notify to Store owner when customer register
- SMS notify to Customer when Invoice created
- SMS notify to Customer when Order canceled
- SMS notify to Customer when credit memo refund
- Customize SMS template
- Test SMS
- Logs all SMS notification

###1 - Installation
##### Manual Installation
 * Download the extension
 * Unzip the file
 * Copy the content from the unzip folder to {Magento Root}/app/code
 * Include Twilio library : ( !warning: this extension works well with twilio 4.10.0)
	+ Install via composer (Recommended), from the command window, type the command:"composer require twilio/sdk 4.10.0"
    + If you want to install via zip file , please go to  "https://www.twilio.com/docs/libraries/php" for more details.

####2 -  Enable extension
 * php -f bin/magento setup:upgrade
 Need to refresh cache after enable extension, please use this command when UI error occur
 * php bin/magento setup:static-content:deploy
####3 - Config extension

Log into your Magento Admin, then goto Report -> Twilio SMS Notification
- Click on Configuration to enter your setting for this function

