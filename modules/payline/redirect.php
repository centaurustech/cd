<?php
/*
http://www.payline.com

Payline module for Prestashop 1.4.x - v1.2 - June 2012

Copyright (c) 2012 Monext
*/

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
		
include "payline.php";
$payline = new payline();

if(isset($_GET['cardInd']) && $_GET['cardInd'] != ''){
	$payline->walletPayment($_GET['cardInd'],$_GET['type']);
	
}
elseif(isset($_POST['directmode']) AND $_POST['directmode'] == 'direct'){
	$payline->directPayment($_POST);
}
else{
	$payline->redirectToPaymentPage($_POST);
}
?>
