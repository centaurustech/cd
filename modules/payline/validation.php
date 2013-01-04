<?php
/*
http://www.payline.com

Payline module for Prestashop 1.4.x - v1.2 - June 2012

Copyright (c) 2012 Monext
*/

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include "include.php";
include "payline.php";
global $cookie;

$array = array();
$paylineSDK = new paylineSDK(
	Configuration::get('PAYLINE_MERCHANT_ID'),
	Configuration::get('PAYLINE_ACCESS_KEY'),
	Configuration::get('PAYLINE_PROXY_HOST'),
	Configuration::get('PAYLINE_PROXY_PORT'),
	Configuration::get('PAYLINE_PROXY_LOGIN'),
	Configuration::get('PAYLINE_PROXY_PASSWORD'),
	Configuration::get('PAYLINE_PRODUCTION') == '1' ? true : false
);
$ModPayline = new payline();

// GET TOKEN
if(isset($_POST['token'])){
	$array['token'] = $_POST['token'];
}elseif(isset($_GET['token'])){
	$array['token'] = $_GET['token'];
}else{
	echo 'Missing TOKEN';
}

$array['version'] = '2';


// RESPONSE FORMAT
$response = $paylineSDK->getWebPaymentDetails($array);



if(isset($response)){
	//WE check if return is Wallet
	if(Tools::getValue('walletInterface'))
	{
		if($response['result']['code'] == "02319")
			echo '<script language="javascript">parent.$.fancybox.close();</script>';
		else
			echo '<script language="javascript">parent.$.fancybox.close();window.parent.location.href="'.__PS_BASE_URI__.'modules/payline/my-wallet.php?success=true";</script>';
	}
	else
	{
		//02319 Payment cancelled by Buyer
		if($response['result']['code'] == "02319")
			Tools::redirectLink(__PS_BASE_URI__.'order.php');

		foreach($response['privateDataList']['privateData'] as $k=>$v)
			$tabPrivateDate[$v->key]	=	$v->value;

		$id_cart = $tabPrivateDate['idCart'];
		$cart = new Cart($id_cart);
		$currency = new Currency($cart->id_currency);
		$customer = new Customer((int)$cart->id_customer);
		$amount	 = $response['payment']['amount']/100;
		$error = "";
		$vars = array();
		if($response['result']['code'] == "00000") {
			$id_order = intval(Order::getOrderByCartId($id_cart));
			$vars['transaction_id'] = $response['transaction']['id'];
			$vars['contract_number'] = $response['payment']['contractNumber'];
			$vars['action'] = $response['payment']['action'];
			$vars['mode'] = $response['payment']['mode'];
			$vars['amount'] = $response['payment']['amount'];
			$vars['currency'] = $response['payment']['currency'];
			$vars['by'] = 'webPayment';
			
			//If amount paid > getTotalCart()
			if($amount > $cart->getOrderTotal())
				$amount = $cart->getOrderTotal();
				
			if(!$id_order)
				$ModPayline->validateOrder($id_cart , _PS_OS_PAYMENT_, $amount , $ModPayline->displayName, $ModPayline->getL('Transaction Payline : ').$response['transaction']['id'].' (web)',$vars,'','',$customer->secure_key);
		}
		else {
			$id_order = intval(Order::getOrderByCartId($id_cart));
			if(!$id_order) {
				if($response['result']['code'] != '02304') {
					if(array_key_exists($response['result']['code'], $tabErrors['Merchant'])) {
						$error = "[".$response['result']['code']."]".$ModPayline->getL($tabErrors['Merchant'][$response['result']['code']]);
						$ModPayline->validateOrder($id_cart, _PS_OS_ERROR_, $amount, $ModPayline->displayName, $ModPayline->getL("[Merchant]")." ".$error.'<br />','','','',$customer->secure_key);
					}
					elseif(array_key_exists($response['result']['code'], $tabErrors['Buyer'])) {
						$error = $ModPayline->getL($tabErrors['Buyer'][$response['result']['code']]);
						$ModPayline->validateOrder($id_cart, _PS_OS_ERROR_, $amount, $ModPayline->displayName, $ModPayline->getL("[Buyer]")." ".$error.'<br />','','','',$customer->secure_key);
					}else{
						$error = 'Payment error';
						$ModPayline->validateOrder($id_cart, _PS_OS_ERROR_, $amount, $ModPayline->displayName, $error.'<br />','','','',$customer->secure_key);
					}
				}
			}
		}
		$id_order = intval(Order::getOrderByCartId($id_cart));
		$order = new Order( $id_order );
		if($response['result']['code'] == "00000") {
			Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart
			.'&id_module='.$ModPayline->id
			.'&key='.$order->secure_key
			);
		}
		else {
			$error = 'Payment error';
			Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart
			.'&id_module='.$ModPayline->id
			.'&key='.$order->secure_key
			.'&error='.$error
			);
		}
	}
}
?>
