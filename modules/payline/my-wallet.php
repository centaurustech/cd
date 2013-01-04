<?php
/*
http://www.payline.com

Payline module for Prestashop 1.4.x - v1.2 - June 2012

Copyright (c) 2012 Monext
*/

/* SSL Management */
$useSSL = true;

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
		
include "include.php";
include "payline.php";
$payline = new payline();

if(!Configuration::get('PAYLINE_WALLET_ENABLE'))
	Tools::redirect('my-account.php');
	
if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=modules/payline/my-wallet.php');

Tools::addCSS(_PS_CSS_DIR_.'jquery.cluetip.css', 'all');
Tools::addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'screen');
Tools::addJS(array(_PS_JS_DIR_.'jquery/jquery.dimensions.js',_PS_JS_DIR_.'jquery/jquery.cluetip.js',_PS_JS_DIR_.'jquery/jquery.fancybox-1.3.4.js'));

//We check action
//Add card or create wallet
if (Tools::isSubmit('createMyWallet'))
{
	if($iframe = $payline->createWallet())
		$smarty->assign(array(
						'iframe' => $iframe));
}

//Delete Card or update
if(Tools::getValue('id_card'))
{
	if(Tools::getValue('delete'))
	{
		if(!$payline->deleteCard(Tools::getValue('id_card')))
			$smarty->assign('error', $payline->getL("ERROR:you can't delete this card."));
	}
	else
	{
		if($iframe = $payline->updateWallet(Tools::getValue('id_card')))
			$smarty->assign(array(
						'iframe' => $iframe));
	}
}

//Delete Wallet
if (Tools::isSubmit('deleteMyWallet'))
{
	if(!$payline->deleteWallet())
		Tools::redirectLink(__PS_BASE_URI__.'modules/payline/my-wallet.php?error=true');
	else
		Tools::redirectLink(__PS_BASE_URI__.'modules/payline/my-wallet.php');
}

include(dirname(__FILE__).'/../../header.php');

if(Tools::getValue('success'))
	$smarty->assign('success', $payline->getL("Operation successfully."));
	
if(Tools::getValue('error'))
	$smarty->assign('error', $payline->getL("ERROR:you can't delete this wallet."));
	
if($payline->getWalletId((int)($cookie->id_customer)))
{
	$cardData = $payline->getMyCards((int)($cookie->id_customer));
	if(is_array($cardData))
		$smarty->assign(array(
						'cardData' => $cardData));
}

if(Configuration::get('PAYLINE_WALLET_PERSONNAL_DATA'))
	$smarty->assign(array(
						'updateData' => true));

echo Module::display(dirname(__FILE__).'/payline.php', 'themes/my-wallet.tpl');

include(dirname(__FILE__).'/../../footer.php');
