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
	
	//02319 Pyment cancelled by Buyer
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
	if($response['result']['code'] == "02500" OR $response['result']['code'] == "00000") {
		$id_order = intval(Order::getOrderByCartId($id_cart));
		$vars['transaction_id'] = $response['transaction']['id'];
		$vars['contract_number'] = $response['payment']['contractNumber'];
		$vars['action'] = $response['payment']['action'];
		$vars['mode'] = $response['payment']['mode'];
		$vars['amount'] = $response['payment']['amount'];
		$vars['currency'] = $response['payment']['currency'];
		$vars['by'] = 'nxPayment';
		
		//We generate message of order
		$numberPayment = 0;
		$message = '<b>'.$ModPayline->getL("[Your schedule]").'</b>'.chr(13).chr(13);
		foreach($response['billingRecordList']['billingRecord'] as $recurring)
		{
			if(!isset($recurring->result))
				$message .= chr(13).$ModPayline->getL("Your next bank levies").'</b>'.chr(13).chr(13); 
			
			$message .= $recurring->date.$ModPayline->getL(":").($recurring->amount/100).' '.$currency->sign;
			if(isset($recurring->result))
			{
				$result = $recurring->result;
				$message .= chr(13).$ModPayline->getL("Result:");
				if($result->code == "00000")
				{	
					$numberPayment++;
					$message .= $ModPayline->getL("Transaction is approved"); 
				}
				else
					$message .= $ModPayline->getL("Transaction is refused");
			}
			$message .= chr(13);
			
			//This variable get the last status of last transaction
			$lastRecurringPayment = $recurring->status;
		}
		
		//If first recurring payment approved
		if(!$id_order) {
			$ModPayline->validateOrder($id_cart , Configuration::get('PAYLINE_ID_ORDER_STATE_NX'), $cart->getOrderTotal() , $ModPayline->displayName, $ModPayline->getL('Transaction Payline : ').$response['transaction']['id'].' (NX)',$vars,'','',$customer->secure_key);
			$id_order = intval(Order::getOrderByCartId($id_cart));
			$order = new Order( $id_order );
			$ModPayline->_addNewMessage((int)($order->id), $message);
			$title = $ModPayline->getL('Recurring payment is approved'); //PAYLINE TRAD
			$customer = new Customer(intval($cart->id_customer));
			$varsTpl = array('{lastname}' => $customer->lastname, '{firstname}' => $customer->firstname, '{id_order}' => $order->id, '{message}' => $message);
			Mail::Send((int)($order->id_lang), 'order_merchant_comment',
									Mail::l('New message regarding your order', (int)($order->id_lang)), $varsTpl, $customer->email,
									$customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL, _PS_MAIL_DIR_, true);
			$varsTpl = array(
				'{lastname}' => $customer->lastname, 
				'{firstname}' => $customer->firstname, 
				'{id_order}' => $id_order,
				'{message}' => $message
				);
			Mail::Send(intval($order->id_lang), 'payment_recurring', $title, $varsTpl, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL, dirname(__FILE__)."/mails/");
		}
		else {
			$order = new Order( $id_order );
			///We update order with message
			if($numberPayment > 1)
				$ModPayline->_addNewMessage((int)($order->id), $message);
			$history = $order->getHistory($cookie->id_lang);
			foreach ($history AS $key=>$row)
			foreach ($row AS $kkey=>$rrow)
			if($kkey == 'id_order_state')
			$tabStates[$rrow] = $rrow;
			
			//If is not the last recurring payment but the current payment is approved we send and email to customer
			if(in_array(Configuration::get('PAYLINE_ID_ORDER_STATE_NX'), $tabStates) && $response['result']['code'] == "02500" && !$lastRecurringPayment && $numberPayment > 1)
			{
				$title = $ModPayline->getL('Recurring payment is approved'); //PAYLINE TRAD
				$customer = new Customer(intval($cart->id_customer));
				$varsTpl = array('{lastname}' => $customer->lastname, '{firstname}' => $customer->firstname, '{id_order}' => $order->id, '{message}' => $message);
				Mail::Send((int)($order->id_lang), 'order_merchant_comment',
									Mail::l('New message regarding your order', (int)($order->id_lang)), $varsTpl, $customer->email,
									$customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL, _PS_MAIL_DIR_, true);
			
				$varsTpl = array(
					'{lastname}' => $customer->lastname, 
					'{firstname}' => $customer->firstname, 
					'{id_order}' => $id_order,
					'{message}' => $message
					);
				Mail::Send(intval($order->id_lang), 'payment_recurring', $title, $varsTpl, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL, dirname(__FILE__)."/mails/");
			}
			
			// If s the last payment approved we change status and ou send an email
			if(in_array(Configuration::get('PAYLINE_ID_ORDER_STATE_NX'), $tabStates) && !in_array(_PS_OS_PAYMENT_, $tabStates) && $lastRecurringPayment == 1) {
				$changeHistory = new OrderHistory();
				$changeHistory->id_order = intval($id_order);
				$changeHistory->changeIdOrderState(_PS_OS_PAYMENT_, intval($id_order));
				$changeHistory->addWithemail();
			}
		}
	}
	else { 
	/************************************************************************************************
	* PAYMENT ERROR
	************************************************************************************************/
		$id_order = intval(Order::getOrderByCartId($id_cart));
		/**If first recurring payment with error**/
		if(!$id_order) {
			if($response['result']['code'] != '02304') { //VERRIFIER LE CODE D'ERREUR
				if(array_key_exists($response['result']['code'], $tabErrors['Merchant'])) {
					$error = "[".$response['result']['code']."]".$ModPayline->getL($tabErrors['Merchant'][$response['result']['code']]);
					$ModPayline->validateOrder($id_cart, _PS_OS_ERROR_, $amount, $ModPayline->displayName, $ModPayline->getL("[Merchant]")." ".$error.'<br />','','','',$customer->secure_key);
				}
				elseif(array_key_exists($response['result']['code'], $tabErrors['Buyer'])) {
					$error = $ModPayline->getL($tabErrors['Buyer'][$response['result']['code']]);
					$ModPayline->validateOrder($id_cart, _PS_OS_ERROR_, $amount, $ModPayline->displayName, $ModPayline->getL("[Buyer]")." ".$error.'<br />','','','',$customer->secure_key);
				}else{
					$error = 'Payment error ['.$response['result']['code'].']';
					$ModPayline->validateOrder($id_cart, _PS_OS_ERROR_, $amount, $ModPayline->displayName, $error.'<br />','','','',$customer->secure_key);
				}
			}
		}
		else
		{
			$order = new Order( $id_order );
			$message .= $ModPayline->getL("Transaction is refused");
			$ModPayline->_addNewMessage((int)($order->id), $message);
			$history = $order->getHistory($cookie->id_lang);
			foreach ($history AS $key=>$row)
			foreach ($row AS $kkey=>$rrow)
			if($kkey == 'id_order_state')
			$tabStates[$rrow] = $rrow;
			if(in_array(_PS_OS_ERROR_, $tabStates) && !in_array(_PS_OS_ERROR_, $tabStates)) {
				$changeHistory = new OrderHistory();
				$changeHistory->id_order = intval($id_order);
				$changeHistory->changeIdOrderState(_PS_OS_ERROR_, intval($id_order));
				$changeHistory->addWithemail();
			}
		}
	
	}
	
	$id_order = intval(Order::getOrderByCartId($id_cart));
	$order = new Order( $id_order );
	if($response['result']['code'] == "02500" OR $response['result']['code'] == "00000") {
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
?>
