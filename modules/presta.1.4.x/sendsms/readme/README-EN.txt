Installation instructions
===============================================
1) Unzip the sendsms module (which is into presta.1.4.x folder) to your module directory
2) Copy files in /sendsms/override/controllers into override/controllers folder of Prestashop
3) Install the module in your shop using the modules tab.
4) Rename your tabs if the SMS tab is not on the same line (make him some place !). Exemple Preferences -> Pref.
5) Create an account and make a deposit on www.smsworldsender.com
6) Fill your identification settings in the SMS tab.
7) If you want the customer to pay for SMS notifications, create a product representing the SMS service (see video http://www.youtube.com/watch?v=4x7x7IDfWjE)
8) Choose your module options, activate events you want on the "Messages management" tab, and if needed, customize their text.
9) Add cron.php to your crontab if you want to receive daily reports.

Optional (should have been done by the installer)
=======================================================
10) Make the change below in the source code
11) Remove your smarty cache for order-carrier.tpl into /tools/smarty/compile/


==========================================================================================================
THESE MODIFICATIONS ARE MADE DURING INSTALLATION.
IN THE "SMS" TAB, YOU CAN CHECK IF EVERYTHING IS OK IN THE "FILES' STATUS" BLOCK
IF YOU DON'T WANT TO USE THE BELOW OPTIONS, DON'T MODIFY ANYTHING
YOU CAN SEE MODIFICATIONS EXAMPLES FOR EACH FILE IN fileschanged.zip
DON'T PASTE THE EXAMPLES FILES, JUST USE THEM TO SEE WHERE ARE LOCATED THE CHANGES TO DO
==========================================================================================================

1. TO ALLOW THE CUSTOMER CHOICE ON THE CARRIER STEP
----------------------------------------------------
- Add this in your theme, file order-carrier.tpl (see order-carrier.tpl in fileschanged.zip)
{$HOOK_SENDSMS_CUSTOMER_CHOICE}

- Customize file sendsms.tpl in the sendsms module, with your own theme


2. TO NOTIFY THE CUSTOMER WHEN A PRODUCT IS NOW AVAILABLE
---------------------------------------------------------
- Add this in mailalerts.php (mailalerts module), into sendCustomerAlert function (see mailalerts.php in changes.zip)
Module::hookExec('sendsmsCustomerAlert', array('customer' => $customer, 'product' => $product));


3. TO SEND A SMS WITH THE SHIPPING NUMBER TO THE CUSTOMER
---------------------------------------------------------
- Add this in the postProcess function in AdminOrder.php in the /youradmin/tabs directory (see AdminOrder.php in changes.zip)
Module::hookExec('sendsmsShippingNumber', array('customer' => $customer, 'order' => $order, 'carrier' => $carrier));