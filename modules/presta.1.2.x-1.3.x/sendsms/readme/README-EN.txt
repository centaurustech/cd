Installation instructions
===============================================
1) Unzip the sendsms module to your module directory
2) Install the module in your shop using the modules tab.
3) Rename your tabs if the SMS tab is not on the same line (make him some place !). Exemple Preferences -> Pref.
4) Create an account and make a deposit on www.smsworldsender.com
5) Fill your identification settings in the SMS tab.
6) If you want the customer to pay for SMS notifications, create a product representing the SMS service (see video http://www.youtube.com/watch?v=4x7x7IDfWjE)
7) Choose your module options, activate events you want on the "Messages management" tab, and if needed, customize their text.
8) Add cron.php to your crontab if you want to receive daily reports.

Optional (should have been done by the installer)
=======================================================
9) Make the change below in the source code
10) Remove your smarty cache for order-carrier.tpl into /tools/smarty/compile/ or opc-order.tpl if you use onepagechekout


==========================================================================================================
THESE MODIFICATIONS ARE MADE DURING INSTALLATION.
IN THE "SMS" TAB, YOU CAN CHECK IF EVERYTHING IS OK IN THE "FILES' STATUS" BLOCK
IF YOU DON'T WANT TO USE THE BELOW OPTIONS, DON'T MODIFY ANYTHING
YOU CAN SEE MODIFICATIONS EXAMPLES FOR EACH FILE IN fileschanged.zip
DON'T PASTE THE EXAMPLES FILES, JUST USE THEM TO SEE WHERE ARE LOCATED THE CHANGES TO DO
==========================================================================================================

1. TO ALLOW THE CUSTOMER CHOICE ON THE CARRIER STEP (only if you're not using onepagecheckout)
----------------------------------------------------------------------------------------------
- Add this in order.php in your Prestashop's home directory, at the beginning of the processCarrier function (see order.php in changes.zip)
if (Module::isInstalled('sendsms')) {
	if (!Module::hookExec('sendsmsCustomerChoice', array('submit' => true, 'customerChoice' => $_POST['sendsms']))) {
		require_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');
		$manager = new sendsmsManager();
	    $errors[] = Tools::displayError($manager->getLabel('customer_phone_error', $cart->id_lang));
	}
}

- Add this in order.php in your Prestashop's home directory, into displayCarrier function (see order.php in changes.zip)
'HOOK_SENDSMS_CUSTOMER_CHOICE' => Module::hookExec('sendsmsCustomerChoice'),

- Add this in order.php in your Prestashop's home directory, at the beginning of the displaySummary function (see order.php in changes.zip)
Module::hookExec('sendsmsCheckCartForSms');

- Add this in your theme, file order-carrier.tpl (see order-carrier.tpl in changes.zip)
{$HOOK_SENDSMS_CUSTOMER_CHOICE}

- Customize file sendsms.tpl in the sendsms module, with your own theme


1-BIS. TO ALLOW THE CUSTOMER CHOICE ON THE CARRIER STEP (only if you're using onepagecheckout)
-----------------------------------------------------------------------------------------------
- Add this in order.php in your Prestashop's home directory, at the end of the processCarrier function (see /onepagecheckout/order.php in changes.zip)
if (Module::isInstalled('sendsms')) {
	if (!Module::hookExec('sendsmsCustomerChoice', array('submit' => true, 'customerChoice' => $_POST['sendsms']))) {
		require_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');
		$manager = new sendsmsManager();
	    $errors[] = Tools::displayError($manager->getLabel('customer_phone_error', $cart->id_lang));
	}
}

- Add this in order.php in your Prestashop's home directory, at the beginning of the displayCarrier function (see /onepagecheckout/order.php in changes.zip)
Module::hookExec('sendsmsCheckCartForSms');

- Add this in order.php in your Prestashop's home directory, into displayCarrier function (see /onepagecheckout/order.php in changes.zip)
'HOOK_SENDSMS_CUSTOMER_CHOICE' => Module::hookExec('sendsmsCustomerChoice'),

- Add this in file opc-order.tpl of your onepagecheckout module (see /onepagecheckout/opc-order.tpl in changes.zip)
{$HOOK_SENDSMS_CUSTOMER_CHOICE}

- Customize file sendsms.tpl in the sendsms module, with your own theme


2. TO BE NOTIFIED, OR THANK CUSTOMER WHEN THEY SEND A MESSAGE USING CONTACT FORM
--------------------------------------------------------------------------------
- Add this in contact-form.php in your Prestashop's home directory (see contact-form.php in changes.zip)
Module::hookExec('sendsmsContactForm', array('contact' => $contact, 'customer' => $customer, 'from' => $from, 'message' => eregi_replace('<br[[:space:]]*/?[[:space:]]*>',chr(13).chr(10),$message)));


3. TO NOTIFY THE CUSTOMER WHEN A PRODUCT IS NOW AVAILABLE
---------------------------------------------------------
- Add this in mailalerts.php (mailalerts module), into sendCustomerAlert function (see mailalerts.php in changes.zip)
Module::hookExec('sendsmsCustomerAlert', array('customer' => $customer, 'product' => $product));


4. TO SEND A SMS WITH THE SHIPPING NUMBER TO THE CUSTOMER
---------------------------------------------------------
- Add this in the postProcess function in AdminOrder.php in the /youradmin/tabs directory (see AdminOrder.php in changes.zip)
Module::hookExec('sendsmsShippingNumber', array('customer' => $customer, 'order' => $order, 'carrier' => $carrier));


5. TO SEND A SMS WITH A NEW PASSWORD TO THE CUSTOMER WHO HAS LOST HIS PREVIOUS PASSWORD
---------------------------------------------------------------------------------------
- Add this in password.php in your Prestashop's home directory (see password.php in changes.zip)
Module::hookExec('sendsmsLostPassword', array('customer' => $customer, 'password' => $password));