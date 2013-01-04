<?php

class OrderController extends OrderControllerCore
{
    protected function _assignCarrier()
    {
       	parent::_assignCarrier();
    	if (!self::$cookie->is_guest) {
        	self::$smarty->assign('HOOK_SENDSMS_CUSTOMER_CHOICE', Module::hookExec('sendsmsCustomerChoice', array('cart' => self::$cart)));
        }
    }

	protected function processCarrier()
	{
		if (!self::$cookie->is_guest && Module::isInstalled('sendsms')) {
			if (Module::hookExec('sendsmsCustomerChoice', array('submit' => true, 'customerChoice' => $_POST['sendsms'])) === -1) {
				require_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');
				$manager = new sendsmsManager();
			    $this->errors[] = Tools::displayError($manager->getLabel('customer_phone_error', self::$cart->id_lang));
			}
		}

		parent::processCarrier();
	}
}
?>