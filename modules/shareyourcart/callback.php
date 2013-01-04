<?php 
session_start();

include(dirname(__FILE__). '/../../config/config.inc.php');
include(dirname(__FILE__). '/../../init.php');
include(dirname(__FILE__). '/shareyourcart.php');


$syc = new ShareYourCartPrestashop();

switch(@$_REQUEST['action'])
{
	case "button":
		$syc->buttonCallback();
		break;
		
	case "coupon":
		$syc->couponCallback();
		break;
		
	default:
		//in case there is no known action, throw an error
		header("HTTP/1.0 404 Unknown action '$_REQUEST[action]'");
}