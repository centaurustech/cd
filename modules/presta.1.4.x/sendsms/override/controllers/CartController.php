<?php

class CartController extends CartControllerCore
{
    public function preProcess()
	{
		$idProduct = (int)Tools::getValue('id_product', NULL);
		if ($idProduct == (int)Configuration::get('SENDSMS_ID_PRODUCT')) {
			$result = self::$cart->containsProduct($idProduct, 0, null);
			if (!empty($result['quantity']) || self::$cookie->is_guest) {
				unset($_POST['add']);
				unset($_GET['add']);
			}
		}

		parent::preProcess();
	}
}
?>