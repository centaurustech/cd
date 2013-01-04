<?php
/*
http://www.payline.com

Payline module for Prestashop 1.4.x - v1.2 - June 2012

Copyright (c) 2012 Monext
*/

/*header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');*/
require_once('configuration/options.php');
require_once('lib/paylineSDK.php');
require_once('lib/lib_debug.php');
//require_once('payline.php');

$tabErrors['Buyer']['01100'] 	= 'Do not honor';
$tabErrors['Buyer']['01101'] 	= 'Card expired';
$tabErrors['Buyer']['01103'] 	= 'Contact your bank for authorization';
$tabErrors['Buyer']['01108'] 	= 'Contact your bank for special condition';
$tabErrors['Buyer']['01111'] 	= 'Invalid card number';
$tabErrors['Buyer']['01113'] 	= 'Expenses not accepted';
$tabErrors['Buyer']['01117'] 	= 'Invalid PIN code';
$tabErrors['Buyer']['01118'] 	= 'Card not registered';
$tabErrors['Buyer']['01119'] 	= 'This transaction is not authorized';
$tabErrors['Buyer']['01120'] 	= 'Transaction refused by terminal';
$tabErrors['Buyer']['01121'] 	= 'Debit limit exceeded';
$tabErrors['Buyer']['01200'] 	= 'Do not honor';
$tabErrors['Buyer']['01201'] 	= 'Card expired';
$tabErrors['Buyer']['01206'] 	= 'Maximum number of attempts reached';
$tabErrors['Buyer']['01208'] 	= 'Card lost';
$tabErrors['Buyer']['01209'] 	= 'Card stolen';
$tabErrors['Buyer']['01915'] 	= 'Transaction is refused';
$tabErrors['Buyer']['02302'] 	= 'Transaction is invalid';

$tabErrors['Merchant']['01109'] = 'Invalid merchant';
$tabErrors['Merchant']['01110'] = 'Invalid amount';
$tabErrors['Merchant']['01114'] = 'This account does not exist';
$tabErrors['Merchant']['01115'] = 'This function does not exist';
$tabErrors['Merchant']['01116'] = 'Amount limit';
$tabErrors['Merchant']['01122'] = 'Security violation';
$tabErrors['Merchant']['01123'] = 'Debit transaction frequency exceeded';
$tabErrors['Merchant']['01125'] = 'Inactive card';
$tabErrors['Merchant']['01126'] = 'Invalid PIN format';
$tabErrors['Merchant']['01128'] = 'Invalid ctrl PIN key';
$tabErrors['Merchant']['01129'] = 'Counterfeith suspected';
$tabErrors['Merchant']['01130'] = 'Invalid cvv2';
$tabErrors['Merchant']['01180'] = 'Invalid bank';
$tabErrors['Merchant']['01181'] = 'Invalid currency';
$tabErrors['Merchant']['01182'] = 'Invalid currency conversion';
$tabErrors['Merchant']['01183'] = 'Max amount exceeded';
$tabErrors['Merchant']['01184'] = 'Max uses exceeded';
$tabErrors['Merchant']['01199'] = 'GTM Internal Error';
$tabErrors['Merchant']['01202'] = 'Fraud suspected';
$tabErrors['Merchant']['01207'] = 'Special condition';
$tabErrors['Merchant']['01280'] = 'Card bin not authorized';
$tabErrors['Merchant']['01902'] = 'Invalid transaction';
$tabErrors['Merchant']['01904'] = 'Bad format request';
$tabErrors['Merchant']['01907'] = 'Card provider server error';
$tabErrors['Merchant']['01909'] = 'Bank server Internal error';
$tabErrors['Merchant']['01912'] = 'Card provider server unknown or unavailable';
$tabErrors['Merchant']['01913'] = 'Transaction already exist';
$tabErrors['Merchant']['01914'] = 'Transaction can not be found';
$tabErrors['Merchant']['01915'] = 'Transaction is refused';
$tabErrors['Merchant']['01917'] = 'This transaction is not resetable';
$tabErrors['Merchant']['01940'] = 'Bank server unavailable';
$tabErrors['Merchant']['01941'] = 'Bank server communication error';
$tabErrors['Merchant']['01942'] = 'Invalid bank server response code';
$tabErrors['Merchant']['01943'] = 'Invalid format for bank server response';
$tabErrors['Merchant']['02101'] = 'Internal Error';
$tabErrors['Merchant']['02102'] = 'External server communication error';
$tabErrors['Merchant']['02103'] = 'Connection timeout, please try later';
$tabErrors['Merchant']['02301'] = 'Transaction ID is invalid';
$tabErrors['Merchant']['02303'] = 'Invalid contract number';
$tabErrors['Merchant']['02304'] = 'No transaction found for this token';
$tabErrors['Merchant']['02305'] = 'Invalid field format';
$tabErrors['Merchant']['02306'] = 'Token is still valid';
$tabErrors['Merchant']['02307'] = 'Invalid custom page code';
$tabErrors['Merchant']['02308'] = 'Invalid value for payment mode';
$tabErrors['Merchant']['02319'] = 'Payment cancelled by the buyer';

?>
