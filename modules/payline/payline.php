<?php
/*
http://www.payline.com

Payline module for Prestashop 1.4.x - v1.2 - June 2012

Copyright (c) 2012 Monext
*/

class payline extends PaymentModule
{
	private $_html = '';
	private $paylineSDK;

	public function __construct()
	{
		$this->name = 'payline';
		$this->tab = 'payments_gateways';
		$this->version = '1.2';
		$this->author = 'Profileo';
		
		parent::__construct();

		$this->displayName = 'Payline';
		$this->description = $this->l('Pay with payline gateway');
		$config = Configuration::getMultiple(array('PAYLINE_MERCHANT_ID', 'PAYLINE_ACCESS_KEY', 'PAYLINE_CONTRACT_NUMBER'));
		if (empty($config['PAYLINE_MERCHANT_ID']) || empty($config['PAYLINE_ACCESS_KEY']) || empty($config['PAYLINE_CONTRACT_NUMBER'])) {
			$warning = $this->l('Missing some parameters : ');
			if(empty($config['PAYLINE_MERCHANT_ID'])) 	  $warning .= $this->l(' - Merchant ID');
			if(empty($config['PAYLINE_ACCESS_KEY']))  	  $warning .= $this->l(' - Access Key');
			if(empty($config['PAYLINE_CONTRACT_NUMBER'])) $warning .= $this->l(' - Contract number');

			$this->warning = $this->l($warning);
		}
		$path = dirname(__FILE__);
		if (strpos(__FILE__, 'Module.php') !== false)
			$path .= '/../modules/'.$this->name;
		include_once($path.'/lib/PaylineClass.php');
		include_once($path.'/include.php');
	}
	
	/*****************************************************************************************
	** INSTALL FUNCTION
	******************************************************************************************/
	public function	install() {
		if (!parent::install()
		|| !$this->registerHook('payment')
		|| !$this->registerHook('paymentReturn')
		|| !$this->installOrderState()
		|| !$this->registerHook('myAccountBlock')
		|| !$this->registerHook('customerAccount')
		|| !$this->installCustomerWallet()
		|| !$this->installPaylineCard()
		|| !$this->installPaylineLang()
		|| !$this->installModuleTab('AdminPayline', array(1 => 'Refunded/Cancellation Payline', 2 => 'Remboursement/annulation Payline'), 3)
		|| !$this->registerHook('AdminOrder')
		|| !$this->registerHook('header')
		|| !$this->registerHook('updateOrderStatus')
		|| !$this->registerHook('cancelProduct'))
		return false;
			
		foreach($this->_getAdminParameters() as $name => $default)
		Configuration::updateValue($name,$default);
		
		return true;
	}

	public function	uninstall()	{
		$idOrderState = Configuration::get('PAYLINE_ID_ORDER_STATE_NX');
		if($idOrderState != '') {
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'order_state` WHERE id_order_state='.$idOrderState);
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'order_state_lang` WHERE id_order_state='.$idOrderState);
		}
		
		if(!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'payline_card`') 
		OR !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'payline`') 
		OR !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'payline_lang`')
		OR !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'payline_order`')  
		OR !$this->unregisterHook('AdminOrder')
		OR !$this->unregisterHook('customerAccount')
		OR !$this->unregisterHook('myAccountBlock')
		OR !$this->unregisterHook('paymentReturn')
		OR !$this->unregisterHook('header')
		OR !$this->unregisterHook('payment')
		OR !$this->unregisterHook('updateOrderStatus')
		OR !$this->uninstallModuleTab('AdminPayline')
		OR !$this->unregisterHook('cancelProduct'))
			return false;
	
		@unlink(_PS_IMG_DIR_.'os/'.$idOrderState.'.gif');
		
		/* Delete all configurations */
		Configuration::deleteByName('PAYLINE_ID_ORDER_STATE_NX');
		foreach($this->_getAdminParameters() as $name => $default)
		Configuration::deleteByName($name);

		return parent::uninstall();
	}
	
	private function uninstallModuleTab($tabClass)
  	{
    	$idTab = Tab::getIdFromClassName($tabClass);
    	if($idTab != 0)
    	{
      		$tab = new Tab($idTab);
      		$tab->delete();
      		return true;
   		}
    	return false;
  	}
  	
	public function installOrderState() {
	
		//PAYLINE ORDER TABLE
		if (!Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'payline_order` (
			`id_order` int(10) unsigned NOT NULL,
			`id_transaction` varchar(255) NOT NULL,
			`contract_number` varchar(255) NOT NULL,
			`payment_status` varchar(255) NOT NULL,
			`mode` varchar(255) NOT NULL,
			`amount` int(10) unsigned NOT NULL,
			`currency` int(10) unsigned NOT NULL,
			`payment_by` varchar(255) NOT NULL,
			PRIMARY KEY (`id_order`))
			ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
		
		if (!Configuration::get('PAYLINE_ID_ORDER_STATE_NX'))
		{
			$orderState = new OrderState();
			$orderState->name = array();
			foreach (Language::getLanguages() AS $language)
			{
				if (strtolower($language['iso_code']) == 'fr')
					$orderState->name[$language['id_lang']] = 'Payé partiellement via Payline';
				else
					$orderState->name[$language['id_lang']] = 'Partially paid through Payline';
			}
			$orderState->send_email = false;
			$orderState->color = '#BBDDEE';
			$orderState->hidden = false;
			$orderState->delivery = false;
			$orderState->logable = true;
			$orderState->invoice = true;
			if ($orderState->add())
				copy(_PS_MODULE_DIR_.$this->name.'/img/orderState.gif', dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif');
			Configuration::updateValue('PAYLINE_ID_ORDER_STATE_NX', (int)$orderState->id);
		}
		
		return true;
	}

	public function installCustomerWallet() {

		$result = Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'payline_wallet` (
											`id_customer` INT( 11 ) NOT NULL ,
											`id_wallet` VARCHAR( 30 ) NOT NULL ,
											UNIQUE (
											`id_customer` ,
											`id_wallet`
											)
											) ENGINE = MYISAM ;');
		if (!$result) return false;

		return true;
	}
	
	public function installPaylineLang() {
	
		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'payline` (
		`id_payline` int(10) unsigned NOT NULL auto_increment,
		PRIMARY KEY (`id_payline`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
			
		if (!Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'payline`(`id_payline`) 
			VALUES(1)'))
		return false;
			
		if (!Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'payline_lang` (
			`id_payline` int(10) unsigned NOT NULL,
			`id_lang` int(10) unsigned NOT NULL,
			`recurring_title` varchar(255) NOT NULL,
			`recurring_subtitle` varchar(255) NOT NULL,
			`direct_title` varchar(255) NOT NULL,
			`wallet_title` varchar(255) NOT NULL,
			PRIMARY KEY (`id_payline`, `id_lang`))
			ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
			
		return true;
	}
			
	public function installPaylineCard() {

		$result = Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'payline_card` (
							`id_card` INT( 2 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
							`type` VARCHAR( 12 ) NOT NULL ,
							`contract` VARCHAR( 12 ) NOT NULL,
							`primary` INT( 1 ) NULL,
							`secondary` INT( 1 ) NULL
							) ENGINE = MYISAM ;');

		if (!$result) return false;
		
		$result = Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'payline_card` (`id_card`, `type`) 
							VALUES (NULL, \'CB\'),
							(NULL, \'VISA\'),
							(NULL, \'MASTERCARD\'),	
							(NULL, \'AMEX\'),
							(NULL, \'SOFINCO\'),
							(NULL, \'DINERS\'),
							(NULL, \'AURORE\'),
							(NULL, \'PASS\'),
							(NULL, \'CBPASS\'),
							(NULL, \'COFINOGA\'),
							(NULL, \'CDGP\'),
							(NULL, \'PRINTEMPS\'),
							(NULL, \'KANGOUROU\'),
							(NULL, \'SURCOUF\'),
							(NULL, \'CYRILLUS\'),
							(NULL, \'FNAC\'),
							(NULL, \'JCB\'),
							(NULL, \'MAESTRO\'),
							(NULL, \'SWITCH\'),
							(NULL, \'1EURO.COM\'),
							(NULL, \'PAYPAL\'),
							(NULL, \'PAYSAFECARD\'),
							(NULL, \'CASINO\'),
							(NULL, \'SKRILL\'),
							(NULL, \'MANDARINE\'),
							(NULL, \'OKSHOPPING\'),
							(NULL, \'TICKETSURF\'),
							(NULL, \'ELV\'),
							(NULL, \'PAYFAIR\'),
							(NULL, \'3XCB\'),
							(NULL, \'IDEAL\'),
							(NULL, \'INTERNET+\')
							;');
		if (!$result) return false;

		return true;
	}
	
	private function installModuleTab($tabClass, $tabName, $idTabParent)
  	{
  		global $cookie;
	    $tab = new Tab();
	    $tab->name = $tabName;
	    $tab->class_name = $tabClass;
	    $tab->module = $this->name;
	    $tab->id_parent = $idTabParent;
	    
	    //We retrieve id_profile
		$employee = new Employee();
		$employee = $employee->getByEmail($cookie->email);
		$cookie->profile = $employee->id_profile;
	    if(!$tab->add())
	   		return false;
	    return true;
  	}
	
	/*****************************************************************************************
	** INSTALL FUNCTION
	******************************************************************************************/
	public function getWalletId($idCust){
		$result = Db::getInstance()->getRow('
			SELECT `id_wallet`
			FROM `'._DB_PREFIX_.'payline_wallet`
			WHERE `id_customer` = '.(int)($idCust));
		
		return isset($result['id_wallet']) ? $result['id_wallet'] : false;
	}
	
	private function getPaylineContracts()
	{
		return Db::getInstance()->ExecuteS('SELECT `contract`,`type`,`primary` FROM `'._DB_PREFIX_.'payline_card` WHERE `contract` <> \'\'');
	}
	
	private function getPaylineContractsSecondary($recurring=false)
	{
		if(!$recurring)
			return Db::getInstance()->ExecuteS('SELECT DISTINCT(`contract`) FROM `'._DB_PREFIX_.'payline_card` WHERE `contract` <> \'\' AND `secondary` = 1');
		else
		{
			$cards = explode(',',Configuration::get('PAYLINE_AUTORIZE_WALLET_CARD'));
			$cardsWhere = array();
			foreach($cards as $key => $value)
				$cardsWhere[] = '\''.$value.'\'';
			return Db::getInstance()->ExecuteS('SELECT DISTINCT(`contract`) FROM `'._DB_PREFIX_.'payline_card` WHERE `contract` <> \'\' AND `secondary` = 1 AND `type` IN('.implode(',',$cardsWhere).')');
		}
	}
	
	private function getPaylineContractByCard($type)
	{
		return Db::getInstance()->getRow('SELECT contract FROM `'._DB_PREFIX_.'payline_card` WHERE `type` = \''.$type.'\'');
	}
	
	public function getMyCards($customer_id)
	{
		$paylineSDK = new paylineSDK(
			Configuration::get('PAYLINE_MERCHANT_ID'), 
			Configuration::get('PAYLINE_ACCESS_KEY'), 
			Configuration::get('PAYLINE_PROXY_HOST'), 
			Configuration::get('PAYLINE_PROXY_PORT'), 
			Configuration::get('PAYLINE_PROXY_LOGIN'), 
			Configuration::get('PAYLINE_PROXY_PASSWORD'), 
			Configuration::get('PAYLINE_PRODUCTION') == '1' ? true : false
		);
		$getCardsRequest = array();
		$getCardsRequest['contractNumber'] = Configuration::get('PAYLINE_CONTRACT_NUMBER');
		$getCardsRequest['walletId'] = $this->getOrGenWalletId($customer_id);
		$getCardsRequest['cardInd'] = null;
		
		$getCardsResponse = $paylineSDK->getCards($getCardsRequest);
		$cardData = array();
		
		if(isset($getCardsResponse) AND is_array($getCardsResponse) AND $getCardsResponse['result']['code'] == '02500'){
			$n = 0;
			foreach ($getCardsResponse['cardsList']['cards'] as $card){
				if(!$card->isDisabled)
				{
                	$cardData[$n] = array();
                	$cardData[$n]['lastName'] = $card->lastName;
                	$cardData[$n]['firstName'] = $card->firstName;
                	$cardData[$n]['number'] = $card->card->number;
                	$cardData[$n]['type'] = $card->card->type;
                	$cardData[$n]['expirationDate'] = $card->card->expirationDate;
                	$cardData[$n]['cardInd'] = $card->cardInd;
                	$n++;
                }
			}
			
			if(sizeof($cardData) > 0)
				return $cardData;
			else
				return false;
		}
		return false;
	}
	
	private function setWalletId($idCust,$idWallet){
		$request = '
			INSERT INTO `'._DB_PREFIX_.'payline_wallet` (
					`id_customer`,
					`id_wallet`
				)VALUES (
					\''.$idCust.'\',
					\''.$idWallet.'\'
				)';
		$result = Db::getInstance()->Execute($request);
		if (!$result) return false;
		return true;
	}
	
	
	/*****************************************************************************************
	** PAYLINE INIT
	******************************************************************************************/
	private function initPaylineSDK(){
		if(!isset($this->paylineSDK )){
			$this->paylineSDK = new paylineSDK(
				Configuration::get('PAYLINE_MERCHANT_ID'),
				Configuration::get('PAYLINE_ACCESS_KEY'),
				Configuration::get('PAYLINE_PROXY_HOST'),
				Configuration::get('PAYLINE_PROXY_PORT'),
				Configuration::get('PAYLINE_PROXY_LOGIN'),
				Configuration::get('PAYLINE_PROXY_PASSWORD'),
				Configuration::get('PAYLINE_PRODUCTION') == '1' ? true : false
			);
		}
	}
	
	private function _getAdminParameters() {
		return array(
			'PAYLINE_AUTORIZE_WALLET_CARD'			=> 'CB,VISA,MASTERCARD,AMEX',
			'PAYLINE_MERCHANT_ID'					=> '',
			'PAYLINE_ACCESS_KEY'					=> '',
			'PAYLINE_CONTRACT_NUMBER'				=> '',
			'PAYLINE_CONTRACT_LIST'					=> '',
			'PAYLINE_PROXY_HOST'					=> '',
			'PAYLINE_PROXY_PORT'					=> '',
			'PAYLINE_PROXY_LOGIN'					=> '',
			'PAYLINE_PROXY_PASSWORD'				=> '',
			'PAYLINE_PRODUCTION'					=> 'FALSE',
			'PAYLINE_SECURITY_MODE'					=> 'SSL',
			'PAYLINE_LANGUAGE_CODE'					=> 'fra',
			'PAYLINE_NB_DAYS_DIFFERED'				=> '0',
			'PAYLINE_CANCEL_URL'					=> 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php',
			'PAYLINE_NOTIFICATION_URL'				=> 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php',
			'PAYLINE_RETURN_URL'					=> 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php',
			'PAYLINE_NOTIFICATION_NX_URL'			=> 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation_nx.php',
			'PAYLINE_RETURN_NX_URL'					=> 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation_nx.php',
			'PAYLINE_DEBUG_MODE'					=> 'FALSE',
			'PAYLINE_WEB_CASH_TPL_URL'				=> '',
			'PAYLINE_WEB_CASH_CUSTOM_CODE'			=> '', 
			'PAYLINE_WEB_CASH_ENABLE'				=> '',
			'PAYLINE_WEB_CASH_MODE'					=> 'CPT',
			'PAYLINE_WEB_CASH_ACTION'				=> '100',
			'PAYLINE_WEB_CASH_VALIDATION'			=> '',
			'PAYLINE_WEB_CASH_BY_WALLET'			=> '',
			'PAYLINE_RECURRING_TPL_URL'				=> '',
			'PAYLINE_RECURRING_CUSTOM_CODE'			=> '', 
			'PAYLINE_RECURRING_ENABLE'				=> '',
			'PAYLINE_RECURRING_ACTION'				=> '101',
			'PAYLINE_RECURRING_BY_WALLET'			=> '',
			'PAYLINE_RECURRING_NUMBER'				=> '2',
			'PAYLINE_RECURRING_PERIODICITY'			=> '10',
			'PAYLINE_RECURRING_MODE'				=> 'NX',
			'PAYLINE_DIRECT_ENABLE'					=> '',
			'PAYLINE_DIRECT_ACTION'					=> '100',
			'PAYLINE_DIRECT_VALIDATION'				=> '',
			'PAYLINE_WALLET_ENABLE'					=> '',
			'PAYLINE_WALLET_ACTION'					=> '100',
			'PAYLINE_WALLET_VALIDATION'				=> '',
			'PAYLINE_WALLET_PERSONNAL_DATA'			=> '',
			'PAYLINE_WALLET_PAYMENT_DATA'			=> '',
			'PAYLINE_WALLET_CUSTOM_CODE'			=> '',
		);
	}
	
	public function getL($key)
	{
		$translations = array(
			'Transaction Payline : ' => $this->l('Transaction Payline : '),
			'Do not honor' => $this->l('Do not honor'),
			'Card expired' => $this->l('Card expired'),
			'Contact  your bank for authorization' => $this->l('Contact your bank for authorization'),
			'Contact your bank for special condition' => $this->l('Contact your bank for special condition'),
			'Invalid card number' => $this->l('Invalid card number'),
			'Expenses not accepted' => $this->l('Expenses not accepted'),
			'Invalid PIN code' => $this->l('Invalid PIN code'),
			'Card not registered' => $this->l('Card not registered'),
			'This transaction is not authorized' => $this->l('This transaction is not authorized'),
			'Transaction refused by terminal' => $this->l('Transaction refused by terminal'),
			'Debit limit exceeded' => $this->l('Debit limit exceeded'),
			'Do not honor' => $this->l('Do not honor'),
			'Card expired' => $this->l('Card expired'),
			'Maximum number of attempts reached' => $this->l('Maximum number of attempts reached'),
			'Card lost' => $this->l('Card lost'),
			'Card stolen' => $this->l('Card stolen'),
			'Transaction is refused' => $this->l('Transaction is refused'),
			'Transaction is invalid' => $this->l('Transaction is invalid'),
			'Transaction is approved' => $this->l('Transaction is approved'),
			'Result:' => $this->l('Result:'),
			':' => $this->l(':'),
			'Your next bank levies' => $this->l('Your next bank levies'),
			'Recurring payment is approved' => $this->l('Recurring payment is approved'),
			'[Merchant]' =>$this->l('[Merchant]'), 
			'[Buyer]' =>$this->l('[Buyer]'),
			'[Your schedule]' =>$this->l('[Your schedule]'),
			'Differed payment accepted' => $this->l('Differed payment accepted'),
			'Payment cancelled by the buyer' => $this->l('Payment cancelled by the buyer'),
			'ERROR:you can\'t delete this card.' => $this->l('ERROR:you can\'t delete this card.'),
			'ERROR:you can\'t delete this wallet.' => $this->l('ERROR:you can\'t delete this wallet.'),
			'Operation successfully.' => $this->l('Operation successfully.')
		);
		return $translations[$key];
	}

	/*****************************************************************************************
	** DISPLAY BACK OFFICE
	******************************************************************************************/
	public function getContent() {
		if (Tools::isSubmit('submitPayline'))
		$this->postProcess();

		$this->_html .= ' <div style="float:left; margin-top:-45px;">
		</div><h2 style="float:left;color:#E53138;">'.$this->l('Avec la solution de paiement en ligne sécurisée Payline, recrutez et fidélisez vos cyber-consommateurs').'</h2><div style="clear: both;"></div>';
		$this->_adminForm();
		return $this->_html;
	}

	public function postProcess()
	{
		global $currentIndex, $cookie;
		if (Tools::isSubmit('submitPaylineRefund'))
		{
			if (!($response = $this->_doTotalRefund((int)(Tools::getValue('id_order')))) OR !sizeof($response))
				$this->_html .= '<p style="color:red;">'.$this->l('Error when making refund request').'</p>';
			else
			{
				if ($response['result']['code'] == '00000')
					Tools::redirectAdmin($currentIndex.'&id_order='.(int)(Tools::getValue('id_order')).'&vieworder&payline=refundOk&token='.Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)($cookie->id_employee)));
				else
					Tools::redirectAdmin($currentIndex.'&id_order='.(int)(Tools::getValue('id_order')).'&vieworder&payline=refundError&token='.Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)($cookie->id_employee)));
			}
		}
		
		if (Tools::isSubmit('submitPaylineCapture'))
		{
			if (!($response = $this->_doTotalCapture((int)(Tools::getValue('id_order')))) OR !sizeof($response))
				$this->_html .= '<p style="color:red;">'.$this->l('Error when making refund request').'</p>';
			else
			{
				if ($response['result']['code'] == '00000')
					Tools::redirectAdmin($currentIndex.'&id_order='.(int)(Tools::getValue('id_order')).'&vieworder&payline=captureOk&token='.Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)($cookie->id_employee)));
				else
					Tools::redirectAdmin($currentIndex.'&id_order='.(int)(Tools::getValue('id_order')).'&vieworder&payline=captureError&token='.Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)($cookie->id_employee)));
			}
		}
		
		if(Tools::isSubmit('submitPayline'))
		{
			$vars = $this->_getAdminParameters();
			foreach ($vars as $var => $default) {
				$value = array_key_exists($var, $_REQUEST) ? $_REQUEST[$var] : null;
				Configuration::updateValue($var, $value);
			}
		
			if(Configuration::get('PAYLINE_WEB_CASH_ACTION') == '101')
				Configuration::updateValue('PAYLINE_WEB_CASH_VALIDATION','');
			if(Configuration::get('PAYLINE_DIRECT_ACTION') == '101')
				Configuration::updateValue('PAYLINE_DIRECT_VALIDATION','');
			if(Configuration::get('PAYLINE_WALLET_ACTION') == '101')
				Configuration::updateValue('PAYLINE_WALLET_VALIDATION','');
				
			if($_POST['paymentMethod'])
			{
				$paymentMethods = $_POST['paymentMethod'];
				foreach($paymentMethods as $key => $value)
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'payline_card` SET `contract` = \''.$value.'\' WHERE `id_card` ='.$key);
				
				if(isset($_POST['primary']))
				{
					$primary = $_POST['primary'];
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'payline_card` SET `primary` = 0');
					foreach($primary as $key => $value)
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'payline_card` SET `primary` = \''.$value.'\' WHERE `id_card` ='.$key);
				}
				
				if(isset($_POST['secondary']))
				{
					$secondary = $_POST['secondary'];
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'payline_card` SET `secondary` = 0');
					foreach($secondary as $key => $value)
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'payline_card` SET `secondary` = \''.$value.'\' WHERE `id_card` ='.$key);
				}
			}
		
			$payline = new PaylineClass(1);
			$payline->copyFromPost();
			$payline->update();
		
			echo '<div class="conf confirm"><img src="../img/admin/ok.gif" />'.$this->l('Settings updated').'</div>';
		}
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="alert error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
		$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '<li>'.$this->l('Please contact your server administrator').'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}

	public function _adminForm()
	{
		if(!extension_loaded('curl')) $this->_postErrors[] = $this->l('php-curl extension is not loaded');
		if(!extension_loaded('soap')) $this->_postErrors[] = $this->l('php-soap extension is not loaded');
		if(!extension_loaded('openssl')) $this->_postErrors[] = $this->l('php-openssl extension is not loaded');

		$this->_displayPayline();
		$this->_html .= '<br />';
		$this->_html .= '<fieldset><legend><img src="'.$this->_path.'img/server.png">&nbsp;'.$this->l('Server configuration').'</legend>';
		$this->_html .= '<table cellpadding="0" cellspacin="0" border="0" width="100%">';
		$this->_html .= '<tr>';
		$this->_html .= '<td valign="middle">';
		if (isset($this->_postErrors) && sizeof($this->_postErrors))
		{
			$this->displayErrors();
		}
		else
		{
			$this->_html .= '<font color="green">'.$this->l('Your server configuration is correct !').'</font>';
		}
		$this->_html .= '</td></tr></table></fieldset><br />';
		$this->_html .='<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">	
			<script type="text/javascript">
				var pos_select = '.(($tab = (int)Tools::getValue('tabs')) ? $tab : '0').';
			</script>
			<script type="text/javascript" src="'._PS_BASE_URL_._PS_JS_DIR_.'tabpane.js"></script>
			<link type="text/css" rel="stylesheet" href="'._PS_BASE_URL_._PS_CSS_DIR_.'tabpane.css" />
			<input type="hidden" name="tabs" id="tabs" value="0" />
			<div class="tab-pane" id="tab-pane-1" style="width:100%;">
				<div class="tab-page" id="step1">
					<h4 class="tab"><img src="'.$this->_path.'img/lock.png">&nbsp;'.$this->l('Payline gateway access').'</h4>
					'.$this->_getPaylineAccessTabHtml().'
				</div>
				<div class="tab-page" id="step2">
					<h4 class="tab"><img src="'.$this->_path.'img/link.png">&nbsp;'.$this->l('Proxy').'</h4>
					'.$this->_getPaylineProxyTabHtml().'
				</div>
				<div class="tab-page" id="step3">
					<h4 class="tab"><img src="'.$this->_path.'img/server.png">&nbsp;'.$this->l('Payment method').'</h4>
					'.$this->_getPaylinePaymentPageTabHtml().'
				</div>
				<div class="tab-page" id="step4">
					<h4 class="tab"><img src="'.$this->_path.'img/money.png">&nbsp;'.$this->l('Contracts').'</h4>
					'.$this->_getPaylinePaymentMethodTabHtml().'
				</div>
				<div class="tab-page" id="step5">
					<h4 class="tab"><img src="'.$this->_path.'img/back.png">&nbsp;'.$this->l('Return to shop').'</h4>
					'.$this->_getPaylineReturnToShopTabHtml().'
				</div>
			</div>
			<div class="clear"></div>
			<script type="text/javascript">
				function loadTab(id){}
				setupAllTabs();
			</script>
			</form>';

		$this->_html .= '<script type="text/javascript">
			function DisplayField(field,showField) {
				var valField = $("#"+field).val();
				if(valField == 1) {
					$("#"+showField).show("normal");
				}
				else if(valField == "100") {
					$("#"+showField).show("normal");
				}
				else {
					$("#"+showField).hide("normal");
				}
			}
		';
		$this->_html .= '</script>';
	}
	
	private function _displayPayline()
	{
		$this->_html .= '<fieldset><legend><img src="../modules/'.$this->name.'/logo.gif" /></legend>
		<div style="float: right; width: 340px; height: 200px; border: dashed 1px #666; padding: 8px; margin-left: 12px; margin-top:-15px;">
			<h2 style="color:#E53138;">'.$this->l('Contactez l\'équipe payline').'</h2>
			<div style="clear: both;"></div>
			<p>'.$this->l('Contactez nous / ou faites vous appeler par un de nos chargés de clientèle :').'<br />'.$this->l('Mail : ').'<a href="mailto:support@payline.com" style="color:#E53138;">support@payline.com</a><br />'.$this->l('Tel : 04 42 25 15 43').'</p>
			<p style="padding-top:5px;"><b>'.$this->l('Faites une demande d`information ou de contrat et nous vous rappelons : ').'</b><br /><a href="http://www.payline.com" target="_blank" style="color:#E53138;">http://www.payline.com</a></p>
			<p><b>'.$this->l('Créez votre compte sans plus attendre : ').'</b><br /><a href="http://www.payline.com/fr/support/tester-payline.html" target="_blank" style="color:#E53138;">http://www.payline.com/fr/support/tester-payline.html</a></p>
		</div>
		<div style="float:left;text-align:justify;margin-top:3px;width:500px;">
      '.$this->l('Simple à installer, ce module vous permet d’intégrer Payline sur votre site et de bénéficier de fonctionnalités inégalées.').'<br /><ul>
      <li>'.$this->l('Plus de ventes : ').'<i>'.$this->l('paiements en 1 clic, paiement en plusieurs fois, 3D Secure débrayable').' </i>'.$this->l('et un accès unique à plus de ').'<b>'.$this->l('50 moyens de paiement…').'</b><br/>'.$this->l('Nouveau : ').'<i>'.$this->l('Leetchi, WeXpay, Paysafecard, Buyster, NeoSurf….').'</i></li>
      <li>'.$this->l('Moins de fraudes : un module unique par sa simplicité et son efficacité. Un exemple : soyez alerté sur vos risques avant d’envoyer vos colis !').'</li>
      <li>'.$this->l('Sur tous les canaux : Payline est nativement sur mobiles, tablettes, SVI…').'</li>
      <li>'.$this->l('Partout : avec Payline,  vous n’avez plus de frontières !').'</li></ul>
      '.$this->l('Vous pouvez accéder au meilleur du paiement en ligne à partir de ').'<b>'.$this->l('15 euros par mois').'</b><br /><br />
      '.$this->l('Pour plus d\'infos :').'<br /><a href="http://www.payline.com" target="_blank" style="color:#E53138;">http://www.payline.com</a>
      </div><div style="clear:both;">&nbsp;</div></fieldset>';
	}
	
	private function _getPaylineAccessTabHtml()
	{
		$html = $this->_adminFormTextinput('PAYLINE_MERCHANT_ID', $this->l('Merchant id'), $this->l('Merchant id provided by the payment gateway'), 'size="40"');
		$html .= $this->_adminFormTextinput('PAYLINE_ACCESS_KEY', $this->l('Access key'), $this->l('Access key provided by the gateway'), 'size="40"');
		$html .= $this->_adminFormTextinput('PAYLINE_CONTRACT_NUMBER', $this->l('Main contract number'),
		$this->l('Your main contract number.')
		);
		$options = array(
				'TRUE'=>$this->l('TRUE '),
				'FALSE'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_DEBUG_MODE'), $options) ? Configuration::get('PAYLINE_DEBUG_MODE') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_DEBUG_MODE', $this->l('Debug mode'),
		$this->l('Enable debug mode to test your configuration.')
		);
		$options = array(
				'1'=>$this->l('TRUE '),
				'0'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_PRODUCTION'), $options) ? Configuration::get('PAYLINE_PRODUCTION') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_PRODUCTION', $this->l('Production mode'),
		$this->l('Enable production.')
		);
		
		$html .= '<p class="center"><input class="button" type="submit" name="submitPayline" value="'.$this->l('Save settings').'" /></p>';
		$html .= '<input type="hidden" name="PAYLINE_AUTORIZE_WALLET_CARD" value="CB,VISA,MASTERCARD,AMEX">';
		return $html;
	}
	
	private function _getPaylineProxyTabHtml()
	{
		$html = $this->_adminFormTextinput('PAYLINE_PROXY_HOST', $this->l('Host'), '', 'size="65"');
		$html .= $this->_adminFormTextinput('PAYLINE_PROXY_PORT', $this->l('Port'), '', 'size="20"');
		$html .= $this->_adminFormTextinput('PAYLINE_PROXY_LOGIN', $this->l('Login'), '', 'size="40"');
		$html .= $this->_adminFormTextinput('PAYLINE_PROXY_PASSWORD', $this->l('Password'), '', 'size="40"');
		
		$html .= '<p class="center"><input class="button" type="submit" name="submitPayline" value="'.$this->l('Save settings').'" /></p>';
		
		return $html;
	}
	
	private function _getPaylinePaymentPageTabHtml()
	{
		global $cookie;
		$states = OrderState::getOrderStates((int)($cookie->id_lang));
		/* Languages preliminaries */
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages(false);
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$divLangName = 'recurringTitle¤recurringSubtitle¤directTitle¤walletTitle';
		
		$payline = new PaylineClass(1);
		
		$options = array(
			  ''=>$this->l('- based on customer browser -'),
				'eng'=>$this->l('English'),
				'spa'=>$this->l('Spanish'),
				'fra'=>$this->l('French'),
				'ita'=>$this->l('Italian')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_LANGUAGE_CODE'), $options) ? Configuration::get('PAYLINE_LANGUAGE_CODE') : 'fra';
		$html = $this->_adminFormSelect($options, $selected, 'PAYLINE_LANGUAGE_CODE', $this->l('Language'),
		$this->l('Default language for the payment gateway')
		);
		
		// PAYMENT WEB CASH
		$html .= '<hr/><h2>'.$this->l('Cash web payment').'</h2>';
		$html .= '<h4>'.$this->l('Check your contracts Payline before activating this mode of payment').'</h4>';
		$options = array(
				'1'=>$this->l('TRUE '),
				'0'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WEB_CASH_ENABLE'), $options) ? Configuration::get('PAYLINE_WEB_CASH_ENABLE') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WEB_CASH_ENABLE', $this->l('Enable'),
		$this->l('Enable for web payment'),0,'cashWeb'
		);
		$html .= '<div id="cashWeb" '.(Configuration::get('PAYLINE_WEB_CASH_ENABLE') != 1 ? 'style="display:none"' : false).'>';
		$options = array(
				'100'=>$this->l('Authorization'),
				'101'=>$this->l('Authorization + Capture')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WEB_CASH_ACTION'), $options) ? Configuration::get('PAYLINE_WEB_CASH_ACTION') : '100';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WEB_CASH_ACTION', $this->l('Action'),
		$this->l('Default action for the payment gateway'),0,'cashWebCapture'
		);
		
		$html .= '<div id="cashWebCapture" '.(Configuration::get('PAYLINE_WEB_CASH_ACTION') == 101 ? 'style="display:none"' : false).'>';
		$options = array('-1'=>'Manual capture');
		foreach ($states AS $state)
			$options[$state['id_order_state']] = stripslashes($state['name']);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WEB_CASH_VALIDATION'), $options) ? Configuration::get('PAYLINE_WEB_CASH_VALIDATION') : '0';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WEB_CASH_VALIDATION', $this->l('Validation'),
		$this->l('Capture your payment to the change of status or manually')
		);
		$html .= '</div>';
		$html .= '<input type="hidden" name="PAYLINE_WEB_CASH_MODE" value="CPT">';
		$html .= $this->_adminFormTextinput('PAYLINE_WEB_CASH_TPL_URL', $this->l('Custom payment template URL'), $this->l('https ://.... Only.'), 'size="65"');
		$html .= $this->_adminFormTextinput('PAYLINE_WEB_CASH_CUSTOM_CODE', $this->l('Custom payment page code'), $this->l('Example : ')."1fd51s2dfs51", 'size="65"');
		$options = array(
				'1'=>$this->l('TRUE '),
				'0'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WEB_CASH_BY_WALLET'), $options) ? Configuration::get('PAYLINE_WEB_CASH_BY_WALLET') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WEB_CASH_BY_WALLET', $this->l('Payment by wallet'),
		$this->l('Payment by wallet')
		);
		$html .= '</div>';
		// END PAYMENT WEB CASH
		
		// WEB PAYMENT IN SEVERAL TIMES
		$html .= '<hr/><h2>'.$this->l('Web payment in several times').'</h2>';
		$html .= '<h4>'.$this->l('Check your contracts Payline before activating this mode of payment').'</h4>';
		$options = array(
				'1'=>$this->l('TRUE '),
				'0'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_RECURRING_ENABLE'), $options) ? Configuration::get('PAYLINE_RECURRING_ENABLE') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_RECURRING_ENABLE', $this->l('Enable'),
		$this->l('Enable for web payment in several times'),0,'timesWeb'
		);
		$html .= '<script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>';
		$html .= '<div id="timesWeb" '.(Configuration::get('PAYLINE_RECURRING_ENABLE') != 1 ? 'style="display:none"' : false).'>';
		$html .='<label style="text-align:left;">'.$this->l('Title').'</label><div class="margin-form">';
		foreach ($languages as $language)
		{
			$html .= '<div id="recurringTitle_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" name="recurring_title_'.$language['id_lang'].'" id="recurring_title_'.$language['id_lang'].'" size="64" value="'.(isset($payline->recurring_title[$language['id_lang']]) ? $payline->recurring_title[$language['id_lang']] : '').'" />
					</div>';
		}
		$html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'recurringTitle', true);
		$html .= '<br class="clear"/><p>'.$this->l('Limited to 255 characters').'</p>';
		$html .= '</div>';
		$html .='<label style="text-align:left;">'.$this->l('Subtitle').'</label><div class="margin-form">';
		foreach ($languages as $language)
		{
			$html .= '<div id="recurringSubtitle_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" name="recurring_subtitle_'.$language['id_lang'].'" id="recurring_subtitle_'.$language['id_lang'].'" size="64" value="'.(isset($payline->recurring_subtitle[$language['id_lang']]) ? $payline->recurring_subtitle[$language['id_lang']] : '').'" />
					</div>';
		}
		$html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'recurringSubtitle', true);
		$html .= '<br class="clear"/><p>'.$this->l('Limited to 255 characters').'</p>';
		$html .= '</div>';
		$html .= $this->_adminFormTextinput('PAYLINE_RECURRING_TPL_URL', $this->l('Custom payment template URL'), $this->l('https ://.... Only.'), 'size="65"');
		$html .= $this->_adminFormTextinput('PAYLINE_RECURRING_CUSTOM_CODE', $this->l('Custom payment page code'), $this->l('Example : ')."1fd51s2dfs51", 'size="65"');
		$options = array();
		for ($i=2;$i<=99;$i++)
		{
			$options[$i] = $i;
		}
		//asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_RECURRING_NUMBER'), $options) ? Configuration::get('PAYLINE_RECURRING_NUMBER') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_RECURRING_NUMBER', $this->l('Number of payments'),
		$this->l('Number of payments')
		);
		$options = array(
				'10'=>$this->l('Daily '),
				'20'=>$this->l('Weekly '),
				'30'=>$this->l('Bimonthly '),
				'40'=>$this->l('Monthly '),
				'50'=>$this->l('Two quaterly '),
				'60'=>$this->l('Quaterly '),
				'70'=>$this->l('Semiannual '),
				'80'=>$this->l('Annual '),
				'90'=>$this->l('Biannual ')
				
		);
		$selected = array_key_exists(Configuration::get('PAYLINE_RECURRING_PERIODICITY'), $options) ? Configuration::get('PAYLINE_RECURRING_PERIODICITY') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_RECURRING_PERIODICITY', $this->l('Periodicity of payments'),
		$this->l('Periodicity of payments')
		);
		$options = array(
				'1'=>$this->l('TRUE '),
				'0'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_RECURRING_BY_WALLET'), $options) ? Configuration::get('PAYLINE_RECURRING_BY_WALLET') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_RECURRING_BY_WALLET', $this->l('Payment by wallet'),
		$this->l('Payment by wallet')
		);
		$html .= '</div>';
		$html .= '<input type="hidden" name="PAYLINE_RECURRING_MODE" value="NX">';
		$html .= '<input type="hidden" name="PAYLINE_RECURRING_ACTION" value="101">';
		// END WEB PAYMENT IN SEVERAL TIMES
		
		// PAYMENT DIRECT
		$html .= '<hr/><h2>'.$this->l('Direct payment').'</h2>';
		$html .= '<h4>'.$this->l('Check your contracts Payline before activating this mode of payment').'</h4>';
		if(!Configuration::get('PS_SHOP_DOMAIN_SSL'))
		{
			$html .='<div class="error">'.$this->l('To enable this option you must have an SSL certificate');
			$html .= '<br/>'.$this->l('When you have your SSL certificate to thank you fill in the field domain name in the SSL tab preferences > SEO & URLs.');
			$html .='</div>';
		}
		if(Configuration::get('PS_SHOP_DOMAIN_SSL'))
		{
			$options = array(
					'1'=>$this->l('TRUE '),
					'0'=>$this->l('FALSE ')
			);
			asort($options);
			$selected = array_key_exists(Configuration::get('PAYLINE_DIRECT_ENABLE'), $options) ? Configuration::get('PAYLINE_DIRECT_ENABLE') : 'false';
			$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_DIRECT_ENABLE', $this->l('Enable'),
			$this->l('Enable for direct payment'),0,'direct'
			);
		}
		else
			$html .= '<input type="hidden" name="PAYLINE_DIRECT_ENABLE" value="0">';
	
		$html .= '<div id="direct" '.((Configuration::get('PAYLINE_DIRECT_ENABLE') != 1 OR !Configuration::get('PS_SHOP_DOMAIN_SSL'))? 'style="display:none"' : false).'>';
		$html .='<label style="text-align:left;">'.$this->l('Title').'</label><div class="margin-form">';
		foreach ($languages as $language)
		{
			$html .= '<div id="directTitle_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" name="direct_title_'.$language['id_lang'].'" id="direct_title_'.$language['id_lang'].'" size="64" value="'.(isset($payline->direct_title[$language['id_lang']]) ? $payline->direct_title[$language['id_lang']] : '').'" />
					</div>';
		}
		$html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'directTitle', true);
		$html .= '<br class="clear"/><p>'.$this->l('Limited to 255 characters').'</p>';
		$html .= '</div>';
		$options = array(
				'100'=>$this->l('Authorization'),
				'101'=>$this->l('Authorization + Capture')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_DIRECT_ACTION'), $options) ? Configuration::get('PAYLINE_DIRECT_ACTION') : '100';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_DIRECT_ACTION', $this->l('Action'),
		$this->l('Default action for the payment gateway'),0,'directCapture'
		);
		$html .= '<div id="directCapture" '.(Configuration::get('PAYLINE_DIRECT_ACTION') == 101 ? 'style="display:none"' : false).'>';
		$options = array('-1'=>'Manual capture');
		foreach ($states AS $state)
			$options[$state['id_order_state']] = stripslashes($state['name']);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_DIRECT_VALIDATION'), $options) ? Configuration::get('PAYLINE_DIRECT_VALIDATION') : '0';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_DIRECT_VALIDATION', $this->l('Validation'),
		$this->l('Capture your payment to the change of status or manually')
		);
		$html .= '</div></div>';
		$html .= '<input type="hidden" name="PAYLINE_DIRECT_MODE" value="CPT">';
		// END PAYMENT DIRECT
		
		// PAYMENT BY WALLET
		$html .= '<hr/><h2>'.$this->l('Payment by Wallet').'</h2>';
		$html .= '<h4>'.$this->l('Check your contracts Payline before activating this mode of payment').'</h4>';
		$options = array(
				'1'=>$this->l('TRUE '),
				'0'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WALLET_ENABLE'), $options) ? Configuration::get('PAYLINE_WALLET_ENABLE') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WALLET_ENABLE', $this->l('Enable'),
		$this->l('Enable for wallet payment'),0,'wallet'
		);
		$html .= '<div id="wallet" '.(Configuration::get('PAYLINE_WALLET_ENABLE') != 1 ? 'style="display:none"' : false).'>';
		$html .='<label style="text-align:left;">'.$this->l('Title').'</label><div class="margin-form">';
		foreach ($languages as $language)
		{
			$html .= '<div id="walletTitle_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" name="wallet_title_'.$language['id_lang'].'" id="wallet_title_'.$language['id_lang'].'" size="64" value="'.(isset($payline->wallet_title[$language['id_lang']]) ? $payline->wallet_title[$language['id_lang']] : '').'" />
					</div>';
		}
		$html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'walletTitle', true);
		$html .= '<br class="clear"/><p>'.$this->l('Limited to 255 characters').'</p>';
		$html .= '</div>';
		$options = array(
				'100'=>$this->l('Authorization'),
				'101'=>$this->l('Authorization + Capture')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WALLET_ACTION'), $options) ? Configuration::get('PAYLINE_WALLET_ACTION') : '100';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WALLET_ACTION', $this->l('Action'),
		$this->l('Default action for the payment gateway'),0,'walletCapture'
		);
		
		$html .= '<div id="walletCapture" '.(Configuration::get('PAYLINE_WALLET_ACTION') == 101 ? 'style="display:none"' : false).'>';
		$options = array('-1'=>'Manual capture');
		foreach ($states AS $state)
			$options[$state['id_order_state']] = stripslashes($state['name']);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WALLET_VALIDATION'), $options) ? Configuration::get('PAYLINE_WALLET_VALIDATION') : '0';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WALLET_VALIDATION', $this->l('Validation'),
		$this->l('Capture your payment to the change of status or manually')
		);
		$html .= '</div>';
		$options = array(
				'1'=>$this->l('TRUE '),
				'0'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WALLET_PERSONNAL_DATA'), $options) ? Configuration::get('PAYLINE_WALLET_PERSONNAL_DATA') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WALLET_PERSONNAL_DATA', $this->l('Update personnal data'),
		$this->l('Allow updating of personal data')
		);
		$options = array(
				'1'=>$this->l('TRUE '),
				'0'=>$this->l('FALSE ')
		);
		asort($options);
		$selected = array_key_exists(Configuration::get('PAYLINE_WALLET_PAYMENT_DATA'), $options) ? Configuration::get('PAYLINE_WALLET_PAYMENT_DATA') : 'false';
		$html .= $this->_adminFormSelect($options, $selected, 'PAYLINE_WALLET_PAYMENT_DATA', $this->l('Update payment data'),
		$this->l('Allow updating of payment data')
		);
		$html .= $this->_adminFormTextinput('PAYLINE_WALLET_CUSTOM_CODE', $this->l('Custom payment page code'), $this->l('Example : ')."1fd51s2dfs51", 'size="65"');
		$html .= '</div>';
		// END PAYMENT BY WALLET
		
		$html .= '<p class="center"><input class="button" type="submit" name="submitPayline" value="'.$this->l('Save settings').'" /></p>';
		$html .= '<input type="hidden" name="PAYLINE_WALLET_MODE" value="CPT">';
		return $html;
	}

	private function _getPaylinePaymentMethodTabHtml()
	{
		$html = '<table cellpadding="0" cellspacin="0" border="0" width="100%">';
		$html .= '<tr>';
		$html .= '<td valign="middle">';

		$paymentMethods = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'payline_card`');
		
		$html .= '<table>';
		
		$i = 0;
		foreach($paymentMethods as $paymentMethod)
		{
			$html .= (($i == 0) ? '<tr>' : '');
			$html .= (($i%3 == 0 AND $i> 0) ? '</tr><tr>' : '').'<td><table cellpadding="5" cellspacing="0"><tr><td colspan="2" style="border-top:1px solid #ddd;border-left:1px solid #ddd;border-right:1px solid #ddd;"><div style="width:190px;color: #7F7F7F;
font-size: 0.85em;"><b>'.$this->l('Contract number ').$paymentMethod['type'].'</b></div></td></tr><tr><td style="border-bottom:1px solid #ddd;border-left:1px solid #ddd;height:70px;"><label style="width:110px;" for="'.$paymentMethod['id_card'].'"><img src="'.__PS_BASE_URI__.'modules/'.$this->name.'/img/'.$paymentMethod['type'].'.gif" border="0" alt="'.$paymentMethod['type'].'" title="'.$paymentMethod['type'].'" /></label></td><td style="border-bottom:1px solid #ddd;border-right:1px solid #ddd;"><div><input id="'.$paymentMethod['id_card'].'" type="text" name="paymentMethod['.$paymentMethod['id_card'].']" value="'.($paymentMethod['contract'] ? $paymentMethod['contract'] : '').'" /></div>';
			$html .= '<div style="color: #7F7F7F;
font-size: 0.85em;"><input type="checkbox" name="primary['.$paymentMethod['id_card'].']" value="1" '.($paymentMethod['primary'] ? 'checked=checked' : '').' /> '.$this->l('Primary').'<input type="checkbox" name="secondary['.$paymentMethod['id_card'].']" value="1" '.($paymentMethod['secondary'] ? 'checked=checked' : '').' style="margin-left:10px;"/> '.$this->l('Second').'</div>';
			$html .= '</td></tr></table></td>';
			$i++;		
		}
		$html .= '</tr></table>';
		$html .= '</td></tr></table>';
		$html .= '<p class="center"><input class="button" type="submit" name="submitPayline" value="'.$this->l('Save settings').'" /></p>';
		
		return $html;
	}
	
	private function _getPaylineReturnToShopTabHtml()
	{
		$html = $this->_adminFormTextinput('PAYLINE_CANCEL_URL', $this->l('Cancel URL'), $this->l('Default cancel URL : ').'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php', 'size="65"');
		$html .= $this->_adminFormTextinput('PAYLINE_NOTIFICATION_URL', $this->l('Notification URL'), $this->l('Default notification URL : ').'
						http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php
					', 'size="65"');
		$html .= $this->_adminFormTextinput('PAYLINE_RETURN_URL', $this->l('Return URL'), $this->l('Default return URL : ').'
						http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php

					', 'size="65"');
		$html .= '<input type="hidden" name="PAYLINE_RETURN_NX_URL" value="'.Configuration::get('PAYLINE_RETURN_NX_URL').'" /><input type="hidden" name="PAYLINE_NOTIFICATION_NX_URL" value="'.Configuration::get('PAYLINE_NOTIFICATION_NX_URL').'" />';			
		$html .= '<p class="center"><input class="button" type="submit" name="submitPayline" value="'.$this->l('Save settings').'" /></p>';
					
		return $html;
	}
	
	private function _adminFormTextinput($name, $label, $description=null, $extra_attributes='', $width = 0) {
		$value = Configuration::get($name);
		$html  = "\n";
		$html .= '<label for="'.$name.'" style="text-align:left; '.($width!=0 ? ' width: '.$width.'px;': false).'">'.$label.'</label>';
		$html .= '<div class="margin-form">';
		$html .= '<input type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.$extra_attributes.'/>';
		$html .= '<p '.($width!=0 ? ' style="margin-left: '.($width - 190).'px;"' : false).'>'.$description.'</p>';
		$html .= '</div>';
		return $html;
	}

	private function _adminFormSelect($options, $selected, $name, $label, $description, $width = 0, $field = '') {
		$arrayToJS = array('PAYLINE_WEB_CASH_ENABLE','PAYLINE_RECURRING_ENABLE','PAYLINE_DIRECT_ENABLE', 'PAYLINE_WALLET_ENABLE', 'PAYLINE_WEB_CASH_ACTION', 'PAYLINE_DIRECT_ACTION', 'PAYLINE_WALLET_ACTION');
		$html = "\n";
		$html .= '<label for="'.$name.'" style="text-align:left; '.($width!=0 ? ' width: '.$width.'px;': false).'">'.$label.'</label>';
		$html .= '<div class="margin-form">';
		$html .= '<select name="'.$name.'" id="'.$name.'" '.(in_array($name,$arrayToJS) ? 'onChange="DisplayField(\''.$name.'\',\''.$field.'\');"' : false).'>';
		foreach($options as $value => $label) {
			$html .= '<option value="'.$value.'"';
			$is_selected = is_array($selected) ? in_array($value,$selected)	: ((string)$value==(string)$selected);
			$html .= $is_selected ? ' selected="selected"' : '';
			$html .= ' style="padding-left: 5px; padding-right:5px">'.$label.'</option>';
		}
		$html .= '</select><p>'.$description.'</p></div>';
		return $html;
	}
	
	/*****************************************************************************************
	** DISPLAY HOOK FUNCTION
	******************************************************************************************/
	public function hookHeader() {	
		Tools::addCSS(($this->_path).'css/payline.css', 'all');
	}
	
	public function hookPayment($params)
	{
		global $smarty, $cart, $cookie;
		
		$cookie 				= $params['cookie'];
		$cart 					= $params['cart'];
		$customer 				= new Customer(intval($cookie->id_customer));
		
		$paymentFrom = '<form action="'.$this->_path.'redirect.php" method="post" name="WebPaymentPayline" id="WebPaymentPayline" class="payline-form">';
		$paymentFrom .= '<input type="hidden" name="contractNumber" id="contractNumber" value="" />';
		$paymentFrom .= '<input type="hidden" name="type" id="type" value="" />';
		$paymentFrom .= '<input type="hidden" name="mode" id="mode" value="" /></form>';
		
		//We retrieve all contract list
		$contracts = $this->getPaylineContracts();
		
		$cardAuthorizeByWalletNx = explode(',',Configuration::get('PAYLINE_AUTORIZE_WALLET_CARD'));
		
		if($contracts)
		{
			$cards = array();
			$cardsNX = array();
			foreach($contracts as $contract)
			{
				if($contract['primary'])
					$cards[] = array('contract' => $contract['contract'],'type' => $contract['type']);
				
				if(in_array($contract['type'],$cardAuthorizeByWalletNx) && $contract['primary'])
					$cardsNX[] = array('contract' => $contract['contract'],'type' => $contract['type']);
			}
		}

		/*if(isset($myCards))
			$smarty->assign('mycards', $myCards);*/
			
		if(isset($cards))
			$smarty->assign('cards', $cards);
	
		if(isset($cardsNX))
			$smarty->assign('cardsNX', $cardsNX);
			
		if(Configuration::get('PAYLINE_WALLET_ENABLE'))
			$cardData = $this->getMyCards($customer->id);
		else
			$cardData = '';
		
		$payline = new PaylineClass(1,(int)$cookie->id_lang);
		$smarty->assign(array(
						'cardData' => $cardData,
						'payline' => $paymentFrom,
						'paylineWebcash' => Configuration::get('PAYLINE_WEB_CASH_ENABLE'),
						'paylineRecurring' => Configuration::get('PAYLINE_RECURRING_ENABLE'), //CB/VISA/MASTERCARD/AMEX
						'paylineDirect' => Configuration::get('PAYLINE_DIRECT_ENABLE'), //CB/VISA/MASTERCARD/AMEX
						'paylineWallet' => Configuration::get('PAYLINE_WALLET_ENABLE'), //CB/VISA/MASTERCARD/AMEX
						'paylineProduction' => Configuration::get('PAYLINE_PRODUCTION'),
						'paylineObj' => $payline));
		return $this->display(__FILE__, 'themes/payline.tpl');
	}

	public function hookPaymentReturn($params)
	{
		global $smarty;
		if (!$this->active)
		return ;
		if(Tools::getValue('error'))
		{
			$smarty->assign(array(
				'error' => Tools::getValue('error')
			));
		}
		else
			$smarty->assign(array(
				'error' => 0
			));
		return $this->display(__FILE__, 'themes/payment_return.tpl');
	}
	
	/**
	* Hook display on customer account page
	* Display an additional link on my-account and block my-account
	*/
	public function hookCustomerAccount($params)
	{
		if(Configuration::get('PAYLINE_WALLET_ENABLE'))
		{
			$contracts = $this->getPaylineContracts();
		
			$cardAuthorizeByWalletNx = explode(',',Configuration::get('PAYLINE_AUTORIZE_WALLET_CARD'));
		
			if($contracts)
			{
				$cards = array();
				foreach($contracts as $contract)
				{
					if(in_array($contract['type'],$cardAuthorizeByWalletNx) && $contract['primary'])
						$cards[] = array('contract' => $contract['contract'],'type' => $contract['type']);
				}
			}
			
			if(sizeof($cards) > 0)
				return $this->display(__FILE__, 'themes/my-account.tpl');
			else
				return false;
		}
		else
			return false;
	}
	
	public function hookMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}
	
	public function hookAdminOrder($params)
	{	
		$html='';
		switch (Tools::getValue('payline'))
		{
			case 'captureOk':
				$message = $this->l('Funds have been recovered.');
				break;
			case 'captureError':
				$message = $this->l('Recovery of funds request unsuccessful. Please see log message!');
				break;
			case 'refundOk':
				$message = $this->l('Refund has been made.');
				break;
			case 'refundError':
				$message = $this->l('Refund request unsuccessful. Please see log message!');
				break;
		}
		if (isset($message) AND $message)
			$html .= '
			<br />
			<div class="module_confirmation conf confirm" style="width: 400px;">
				<img src="'._PS_IMG_.'admin/ok.gif" alt="" title="" /> '.$message.'
			</div>';
			
		//If order paid by Payline
		if($this->_canRefund((int)$params['id_order']))
		{
			$html .= '<br />
				<fieldset style="width:400px;">
					<legend><img src="'._MODULE_DIR_.$this->name.'/logo.gif" alt="" /> '.$this->l('Payline Refund').'</legend>
					<p><b>'.$this->l('Information:').'</b> '.$this->l('Payment accepted').'</p>
					<p><b>'.$this->l('Information:').'</b> '.$this->l('When you refund a product, a partial refund is made unless you select "Generate a voucher".').'</p>
					<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">
						<input type="hidden" name="id_order" value="'.(int)$params['id_order'].'" />
						<p class="center"><input type="submit" class="button" name="submitPaylineRefund" value="'.$this->l('Refund total transaction').'" onclick="if(!confirm(\''.$this->l('Are you sure?').'\'))return false;" /></p>
					</form>';
			$html .= '</fieldset>';
			$this->postProcess();
			return $html;
		}
		
		if($this->_canCapture((int)$params['id_order']))
		{
			$html .= '<br />
				<fieldset style="width:400px;">
					<legend><img src="'._MODULE_DIR_.$this->name.'/logo.gif" alt="" /> '.$this->l('Payline Capture').'</legend>
					<p><b>'.$this->l('Information:').'</b> '.$this->l('Authorized payment').'</p>
					<p><b>'.$this->l('Information:').'</b> '.$this->l('You can capture this transaction manually').'</p>
					<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">
						<input type="hidden" name="id_order" value="'.(int)$params['id_order'].'" />
						<p class="center"><input type="submit" class="button" name="submitPaylineCapture" value="'.$this->l('Capture total transaction').'" onclick="if(!confirm(\''.$this->l('Are you sure?').'\'))return false;" /></p>
					</form>';
			$html .= '</fieldset>';
			$this->postProcess();
		}
		
		return $html;
	}
    
    public function hookCancelProduct($params)
	{
		$order = $params['order'];
		if($this->_canRefund((int)$order->id))
		{
			if (Tools::isSubmit('generateDiscount'))
				return false;
			if ($params['order']->module != $this->name)
				return false;
			if (!($order = $params['order']) OR !Validate::isLoadedObject($order))
				return false;
			if (!$order->hasBeenPaid())
				return false;
			if (!($order_detail = new OrderDetail((int)($params['id_order_detail']))) OR !Validate::isLoadedObject($order_detail))
				return false;

			$id_transaction = $this->_getTransactionId((int)($order->id));
			if (!$id_transaction)
				return false;
		
			$contract_number = $this->_getContractNumberByTransaction((int)($order->id));
			if (!$contract_number)
				return false;
			
			$products = $order->getProducts();
			if($products[(int)($order_detail->id)]['product_quantity_discount'] == 0)
				$amt = round(($products[(int)($order_detail->id)]['product_price_wt'] * (int)($_POST['cancelQuantity'][(int)($order_detail->id)])),2);
			else
				$amt = $products[(int)($order_detail->id)]['product_quantity_discount'] * (int)($_POST['cancelQuantity'][(int)($order_detail->id)]);
			
			$response = $this->_makeRefund($id_transaction, $contract_number, (int)$order->id, (float)($amt));
			$message = $this->l('Cancel products result:').'<br>';
			
			if(isset($response) && $response['result']['code'] == '00000'){
				$message .= $this->l('Payline refund successful!');
				$message .= $this->l('Transaction ID:').$response['transaction']['id'];
			}
			else
				$message .= $this->l('Payline refund invalid!');
			$this->_addNewPrivateMessage((int)$order->id, $message);
		}
		else
			return false;
	}
	
	public function hookUpdateOrderStatus($params)
	{
		if($this->_canCapture((int)$params['id_order']))
		{
			//We verify if capture is manual or by status
			switch ($this->_getPaymenByOder((int)$params['id_order']))
			{
				case 'webPayment' :
					$state = Configuration::get('PAYLINE_WEB_CASH_VALIDATION');
				break;
				
				case 'directPayment' :
					$state = Configuration::get('PAYLINE_DIRECT_VALIDATION');
				break;
				
				case 'walletPayment' :
					$state = Configuration::get('PAYLINE_WALLET_VALIDATION');
				break;
			}
			
			if(isset($state) && $state > -1)
			{
				$orderState = $params['newOrderStatus'];
				if($state == $orderState->id)
					$this->_doTotalCapture((int)$params['id_order']);
			}
		}
	}
	
	/*****************************************************************************************
	** BACK OFFICE MANAGEMENT
	******************************************************************************************/
	private function _canRefund($id_order)
	{
		if (!(int)$id_order)
			return false;
		$payline_order = Db::getInstance()->getRow('
		SELECT * 
		FROM `'._DB_PREFIX_.'payline_order` 
		WHERE `id_order` = '.(int)$id_order);
		if (!is_array($payline_order) OR !sizeof($payline_order))
			return false;
		if ($payline_order['payment_status'] != 'capture')
			return false;
		return true;
	}
	
	private function _canCapture($id_order)
	{
		if (!(int)$id_order)
			return false;
		$payline_order = Db::getInstance()->getRow('
		SELECT * 
		FROM `'._DB_PREFIX_.'payline_order` 
		WHERE `id_order` = '.(int)$id_order);
		if (!is_array($payline_order) OR !sizeof($payline_order))
			return false;
		if ($payline_order['payment_status'] != 'authorization')
			return false;
		return true;
	}
	
	private function _getTransactionId($id_order)
	{
		if (!(int)$id_order)
			return false;
		
		return Db::getInstance()->getValue('
		SELECT `id_transaction` 
		FROM `'._DB_PREFIX_.'payline_order` 
		WHERE `id_order` = '.(int)$id_order);
	}
	
	private function _getContractNumberByTransaction($id_order)
	{
		if (!(int)$id_order)
			return false;
		
		return Db::getInstance()->getValue('
		SELECT `contract_number` 
		FROM `'._DB_PREFIX_.'payline_order` 
		WHERE `id_order` = '.(int)$id_order);
	}
	
	private function _getAmountByTransaction($id_order)
	{
		if (!(int)$id_order)
			return false;
		
		return Db::getInstance()->getValue('
		SELECT `amount` 
		FROM `'._DB_PREFIX_.'payline_order` 
		WHERE `id_order` = '.(int)$id_order);
	}
	
	private function _getPaymenByOder($id_order)
	{
		if (!(int)$id_order)
			return false;
		
		return Db::getInstance()->getValue('
		SELECT `payment_by` 
		FROM `'._DB_PREFIX_.'payline_order` 
		WHERE `id_order` = '.(int)$id_order);
	}
	
	private function _addNewPrivateMessage($id_order, $message)
	{
		if (!$id_order)
			return false;
		$msg = new Message();
		$message = strip_tags($message, '<br>');
		if (!Validate::isCleanHtml($message))
			$message = $this->l('Payment message is not valid, please check your module.');
		$msg->message = $message;
		$msg->id_order = (int)($id_order);
		$msg->private = 1;

		return $msg->add();
	}
	
	public function _addNewMessage($id_order, $message)
	{
		if (!$id_order)
			return false;
		$order = new Order($id_order);
		$msg = new Message();
		$message = strip_tags($message, '<br>');
		if (!Validate::isCleanHtml($message))
			$message = $this->l('Payment message is not valid, please check your module.');
		$msg->message = $message;
		$msg->id_order = (int)($id_order);
		$msg->id_cart = (int)($order->id_cart);
		$msg->id_employee = _PS_ADMIN_PROFILE_;
		$msg->private = 0;

		return $msg->add();
	}
	
	private function _doTotalRefund($id_order)
	{
		global $cookie;
		
		if (!$id_order)
			return false;

		$id_transaction = $this->_getTransactionId((int)($id_order));
		if (!$id_transaction)
			return false;
		
		$contract_number = $this->_getContractNumberByTransaction((int)($id_order));
		if (!$contract_number)
			return false;
			
		$order = new Order((int)($id_order));
		if (!Validate::isLoadedObject($order))
			return false;
			
		$products = $order->getProducts();
		// Amount for refund
		$amt = 0.00;
		foreach ($products AS $product)
		{
			if ($product['product_quantity_refunded'] == 0)
				$amt += round(($product['product_price_wt']*$product['product_quantity']),2);
			else
				$amt += round(($product['product_price_wt']*($product['product_quantity']-$product['product_quantity_refunded'])),2);
		}
		
		$amt += (float)($order->total_shipping);
		// check if total or partial
		if ($order->total_products_wt == $amt)
			$response = $this->_makeRefund($id_transaction, $contract_number, (int)($id_order));
		else
			$response = $this->_makeRefund($id_transaction, $contract_number, (int)($id_order), (float)($amt));
		$message = $this->l('Refund operation result:').'<br>';
		
		if(isset($response) && $response['result']['code'] == '00000'){
			$message .= $this->l('Payline refund successful!');
			$message .= $this->l('Transaction ID:').$response['transaction']['id'];
			if (!Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'payline_order` SET `payment_status` = \'Refunded\',  `id_transaction` = \''.$response['transaction']['id'].'\' WHERE `id_order` = '.(int)($id_order)))
				die(Tools::displayError('Error when updating Payline database'));
			$history = new OrderHistory();
			$history->id_order = (int)($id_order);
			$history->changeIdOrderState(_PS_OS_REFUND_, (int)($id_order));
			$history->addWithemail();
		}
		else
			$message .= $this->l('Transaction error!');
		$this->_addNewPrivateMessage((int)($id_order), $message);
		
		return $response;
	}
	
	private function _doTotalCapture($id_order)
	{
		global $cookie;
		
		if (!$id_order)
			return false;

		$id_transaction = $this->_getTransactionId((int)($id_order));
		if (!$id_transaction)
			return false;
		
		$contract_number = $this->_getContractNumberByTransaction((int)($id_order));
		if (!$contract_number)
			return false;
			
		$amount = $this->_getAmountByTransaction((int)($id_order));
		if (!$amount)
			return false;
			
		$order = new Order((int)($id_order));
		if (!Validate::isLoadedObject($order))
			return false;
			
		
		// check if the payment was made there more than 7 days
		if(strtotime($order->date_add) > mktime(23,59,59,date('m'),date('d')-7,date('Y')))
			$response = $this->_makeCapture($id_transaction, $contract_number, (int)($id_order), (int)($amount));
		else
			$response = $this->_makeReauthorization($id_transaction, $contract_number, (int)($id_order), (int)($amount));
		
		$message = $this->l('Capture operation result:').'<br>';
		
		if(isset($response) && $response['result']['code'] == '00000'){
			$message .= $this->l('Payline capture successful!');
			$message .= $this->l('Transaction ID:').$response['transaction']['id'];
			if (!Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'payline_order` SET `payment_status` = \'capture\' WHERE `id_order` = '.(int)($id_order)))
				die(Tools::displayError('Error when updating Payline database'));
		}
		else
			$message .= $this->l('Transaction capture error!');
			
		$this->_addNewPrivateMessage((int)($id_order), $message);
		
		return $response;
	}
	
	private function _makeRefund($id_transaction, $contract_number, $id_order, $amt = false)
	{
		$this->initPaylineSDK();
		
		$order = new Order($id_order);
		$cart = new Cart($order->id_cart);
		
		$currency 	 	 = new Currency(intval($cart->id_currency));
		
		if(!$amt)
			$doWebRefundRequest['payment']['amount'] = round($order->total_paid_real*100);
		else
			$doWebRefundRequest['payment']['amount'] = round($amt*100);
		
		$doWebRefundRequest['transactionID'] 					= $id_transaction;
		$doWebRefundRequest['payment']['currency'] 				= $currency->iso_code_num;
		$doWebRefundRequest['payment']['contractNumber']		= $contract_number;
		$doWebRefundRequest['payment']['mode']					= 'CPT';
		$doWebRefundRequest['payment']['action']				= 421;
		$doWebRefundRequest['payment']['differedActionDate']	= '';
		$doWebRefundRequest['sequenceNumber'] 					= '';
		$doWebRefundRequest['comment'] 							= $this->l('Refund from the back office Prestashop');
		
		$result = $this->paylineSDK->doRefund($doWebRefundRequest);
		
		return $result;
	}
	
	private function _makeCapture($id_transaction, $contract_number, $id_order, $amount)
	{
		$this->initPaylineSDK();
		
		$order = new Order($id_order);
		$cart = new Cart($order->id_cart);
		
		$currency 	 	 = new Currency(intval($cart->id_currency));
		
		$doWebCaptureRequest['transactionID'] 					= $id_transaction;
		$doWebCaptureRequest['payment']['currency'] 			= $currency->iso_code_num;
		$doWebCaptureRequest['payment']['contractNumber']		= $contract_number;
		$doWebCaptureRequest['payment']['amount']				= $amount;
		$doWebCaptureRequest['payment']['mode']					= 'CPT';
		$doWebCaptureRequest['payment']['action']				= 201;
		$doWebCaptureRequest['payment']['differedActionDate']	= '';
		$doWebCaptureRequest['sequenceNumber'] 					= '';
		$doWebCaptureRequest['comment'] 						= $this->l('Capture from the back office Prestashop');
		
		$result = $this->paylineSDK->doCapture($doWebCaptureRequest);
		
		return $result;
	}
	
	private function _makeReauthorization($id_transaction, $contract_number, $id_order, $amount)
	{
		$this->initPaylineSDK();
		
		$order = new Order($id_order);
		$cart = new Cart($order->id_cart);
		
		$currency 	 	 = new Currency(intval($cart->id_currency));
		
		$doWebCaptureRequest['transactionID'] 					= $id_transaction;
		$doWebCaptureRequest['payment']['currency'] 			= $currency->iso_code_num;
		$doWebCaptureRequest['payment']['contractNumber']		= $contract_number;
		$doWebCaptureRequest['payment']['amount']				= $amount;
		$doWebCaptureRequest['payment']['mode']					= 'CPT';
		$doWebCaptureRequest['payment']['action']				= 101;
		$doWebCaptureRequest['payment']['differedActionDate']	= '';
		$doWebCaptureRequest['sequenceNumber'] 					= '';
		$doWebCaptureRequest['comment'] 						= $this->l('Capture from the back office Prestashop');
		$doWebCaptureRequest['order']['ref'] 					= $currency->iso_code_num;
		$doWebCaptureRequest['order']['origin']					= $contract_number;
		$doWebCaptureRequest['order']['country']				= $contract_number;
		$doWebCaptureRequest['order']['taxes']					= 'CPT';
		$doWebCaptureRequest['order']['amount']					= $amount;
		$doWebCaptureRequest['order']['currency']				= $currency;
		$doWebCaptureRequest['order']['date']					= $order->date_add;
		
		$result = $this->paylineSDK->doReAuthorization($doWebCaptureRequest);
		
		return $result;
	}
	
	
	/*****************************************************************************************
	** FRONT OFFICE MANAGEMENT
	******************************************************************************************/
	public function redirectToPaymentPage($paylineVars=NULL){
		global $cart, $cookie;
		$this->initPaylineSDK();
		
		$address 				= new Address($cart->id_address_invoice);
		$cust 					= new Customer(intval($cookie->id_customer));
		$pays 					= new Country($address->id_country);
		$DataAddressDelivery 	= new Address($cart->id_address_delivery);
		
		$currency 	 	 = new Currency(intval($cart->id_currency));
		
		$orderTotalWhitoutTaxes			= round($cart->getOrderTotal(false)*100);
		$orderTotal						= round($cart->getOrderTotal()*100);
		$taxes							= $orderTotal-$orderTotalWhitoutTaxes;
		
		$newOrderId = $this->l('My cart').' [num '.$cart->id.']';
				
		$doWebPaymentRequest = array();
		
		$cardAuthoriseWallet = explode(',',Configuration::get('PAYLINE_AUTORIZE_WALLET_CARD'));
		
		$secondaryCardList = array();
		if($paylineVars['mode'] == 'recurring')
			$secondaryCards = $this->getPaylineContractsSecondary(true);
		else
			$secondaryCards = $this->getPaylineContractsSecondary();
		
		foreach($secondaryCards as $secondaryCard)
			$secondaryCardList[] = $secondaryCard['contract'];
		
		//Retrieve payment mode
		switch($paylineVars['mode'])
		{
			case 'webCash' :
				$payment_mode = Configuration::get('PAYLINE_WEB_CASH_MODE');
				$payment_action = Configuration::get('PAYLINE_WEB_CASH_ACTION');
				if(Configuration::get('PAYLINE_WEB_CASH_BY_WALLET') AND in_array($paylineVars['type'],$cardAuthoriseWallet))
					$payment_by_wallet = true;
			break;
			
			case 'recurring' :
				$payment_mode = Configuration::get('PAYLINE_RECURRING_MODE');
				$payment_action = Configuration::get('PAYLINE_RECURRING_ACTION');
				if(Configuration::get('PAYLINE_RECURRING_BY_WALLET') AND in_array($paylineVars['type'],$cardAuthoriseWallet))
					$payment_by_wallet = true;
			break;
				
		}
		
		// CASE RECURRING (TIMES)
		if($paylineVars['mode'] == 'recurring')
		{
			$doWebPaymentRequest['recurring']['amount'] 		= round($orderTotal/Configuration::get('PAYLINE_RECURRING_NUMBER'));
			$doWebPaymentRequest['recurring']['firstAmount'] 	= $orderTotal - ($doWebPaymentRequest['recurring']['amount'] * (Configuration::get('PAYLINE_RECURRING_NUMBER')-1));
			$doWebPaymentRequest['recurring']['billingCycle']	= Configuration::get('PAYLINE_RECURRING_PERIODICITY');
			$doWebPaymentRequest['recurring']['billingLeft']	= Configuration::get('PAYLINE_RECURRING_NUMBER');
			$doWebPaymentRequest['recurring']['billingDay']		= '';
			$doWebPaymentRequest['recurring']['startDate']		= '';
		}
		
		// PAYMENT
		if($paylineVars['mode'] != 'recurring')
			$doWebPaymentRequest['payment']['amount'] 			= $orderTotal;
		else
			$doWebPaymentRequest['payment']['amount'] 			= $doWebPaymentRequest['recurring']['firstAmount']+($doWebPaymentRequest['recurring']['billingLeft']-1)*$doWebPaymentRequest['recurring']['amount'];
			
		$doWebPaymentRequest['payment']['currency'] 		= $currency->iso_code_num;
		$doWebPaymentRequest['payment']['contractNumber']	= $paylineVars['contractNumber'];
		$doWebPaymentRequest['payment']['mode']				= $payment_mode;
		$doWebPaymentRequest['payment']['action']			= $payment_action;
		
		// ORDER
		$doWebPaymentRequest['order']['ref'] 				= $newOrderId;
		$doWebPaymentRequest['order']['country'] 			= $pays->iso_code;
		$doWebPaymentRequest['order']['taxes'] 				= $taxes;
		$doWebPaymentRequest['order']['amount'] 			= $orderTotalWhitoutTaxes;
		$doWebPaymentRequest['order']['date'] 				= date('d/m/Y H:i');
		$doWebPaymentRequest['order']['currency'] 			= $doWebPaymentRequest['payment']['currency'];
		
		// ORDER DETAILS
		foreach($cart->getProducts() as $productDetail) {
			if(round($productDetail['price']*100) > 0)
			{
				$item = array();
				$item['ref'] 				= $productDetail['reference'];
				$item['price'] 				= round($productDetail['price']*100);
				$item['quantity'] 			= $productDetail['cart_quantity'];
				$item['comment'] 			= $productDetail['name'];
				$this->paylineSDK->setItem($item);
			}
		}
		
		// PRIVATE DATA
		$privateData0 = array();
		$privateData0['key'] 					= 'idOrder';
		$privateData0['value'] 					= $newOrderId;
		$this->paylineSDK->setPrivate($privateData0);
		$privateData1 = array();
		$privateData1['key'] 					= 'idCart';
		$privateData1['value'] 					= $cart->id;
		$this->paylineSDK->setPrivate($privateData1);
		$privateData2 = array();
		$privateData2['key'] 					= 'idCust';
		$privateData2['value'] 					= $cust->id;
		$this->paylineSDK->setPrivate($privateData2);
		
		$doWebPaymentRequest['contracts'] = array($paylineVars['contractNumber']);
		$doWebPaymentRequest['secondContracts'] = $secondaryCardList;
		
		// BUYER
		$doWebPaymentRequest['buyer']['lastName'] 	= $cust->lastname;
		$doWebPaymentRequest['buyer']['firstName'] 	= $cust->firstname;
		$doWebPaymentRequest['buyer']['email'] 		= $cust->email;
		
		if(isset($payment_by_wallet) AND $payment_by_wallet)
			$doWebPaymentRequest['buyer']['walletId'] 	= $this->getOrGenWalletId($cust->id);
		else
			$doWebPaymentRequest['buyer']['walletId']	= "";
		
		// ADDRESS
		$doWebPaymentRequest['address']['name'] 	= $DataAddressDelivery->alias;
		$doWebPaymentRequest['address']['street1'] 	= $DataAddressDelivery->address1;
		$doWebPaymentRequest['address']['street2'] 	= $DataAddressDelivery->address2;
		$doWebPaymentRequest['address']['cityName'] = $DataAddressDelivery->city;
		$doWebPaymentRequest['address']['zipCode'] 	= $DataAddressDelivery->postcode;
		$doWebPaymentRequest['address']['country'] 	= $DataAddressDelivery->country;
		$forbidenCars = array(' ','.','(',')','-');
		$doWebPaymentRequest['address']['phone'] 	= str_replace($forbidenCars,'',$DataAddressDelivery->phone);
		
		// URLs
		if($paylineVars['mode'] == 'recurring')
		{
			$this->paylineSDK->returnURL = Configuration::get('PAYLINE_RETURN_NX_URL');
			$this->paylineSDK->cancelURL = Configuration::get('PAYLINE_CANCEL_URL');
			$this->paylineSDK->notificationURL = Configuration::get('PAYLINE_NOTIFICATION_NX_URL');
		}
		else
		{
			$this->paylineSDK->returnURL = Configuration::get('PAYLINE_RETURN_URL');
			$this->paylineSDK->cancelURL = Configuration::get('PAYLINE_CANCEL_URL');
			$this->paylineSDK->notificationURL = Configuration::get('PAYLINE_NOTIFICATION_URL');
		}
		
		// Customization
		if($paylineVars['mode'] == 'recurring')
		{
			$this->paylineSDK->customPaymentPageCode = Configuration::get('PAYLINE_RECURRING_CUSTOM_CODE');
			$this->paylineSDK->customPaymentTemplateURL = Configuration::get('PAYLINE_RECURRING_TPL_URL');
		}
		else
		{
			$this->paylineSDK->customPaymentPageCode = Configuration::get('PAYLINE_WEB_CASH_CUSTOM_CODE');
			$this->paylineSDK->customPaymentTemplateURL = Configuration::get('PAYLINE_WEB_CASH_TPL_URL');
		}
		
		$result = $this->paylineSDK->doWebPayment($doWebPaymentRequest);
		
		//Debug mode
		if(strtoupper(Configuration::get('PAYLINE_DEBUG_MODE')) == "TRUE") {
			print_a($result, 0, true);
			exit();
		}
		
		if(isset($result) && $result['result']['code'] == '00000'){
			header("location:".$result['redirectURL']);
			exit();
		}
		elseif(isset($result)) {
			echo 'ERROR : '.$result['result']['code']. ' '.$result['result']['longMessage'].' <BR/>';
		}
	}
	
	public function directPayment($paylineVars=NULL){
		global $cart, $cookie;
		$this->initPaylineSDK();
		
		$address 				= new Address($cart->id_address_invoice);
		$cust 					= new Customer(intval($cookie->id_customer));
		$pays 					= new Country($address->id_country);
		$DataAddressDelivery 	= new Address($cart->id_address_delivery);
		
		$currency 	 	 = new Currency(intval($cart->id_currency));
		
		$orderTotalWhitoutTaxes			= round($cart->getOrderTotal(false)*100);
		$orderTotal						= round($cart->getOrderTotal()*100);
		$taxes							= $orderTotal-$orderTotalWhitoutTaxes;
		
		$newOrderId = $this->l('My cart').' [num '.$cart->id.']';
				
		$directPaymentRequest = array();
		
		$cardAuthoriseWallet = explode(',',Configuration::get('PAYLINE_AUTORIZE_WALLET_CARD'));
		
		
		// PAYMENT
		$directPaymentRequest['payment']['amount'] 			= $orderTotal;
			
		$directPaymentRequest['payment']['currency'] 		= $currency->iso_code_num;
		$directPaymentRequest['payment']['contractNumber']	= $paylineVars['contractNumber'];
		$directPaymentRequest['payment']['mode']				= 'CPT';
		$directPaymentRequest['payment']['action']			= Configuration::get('PAYLINE_DIRECT_ACTION');
		
		// ORDER
		$directPaymentRequest['order']['ref'] 				= $newOrderId;
		$directPaymentRequest['order']['country'] 			= $pays->iso_code;
		$directPaymentRequest['order']['taxes'] 				= $taxes;
		$directPaymentRequest['order']['amount'] 			= $orderTotalWhitoutTaxes;
		$directPaymentRequest['order']['date'] 				= date('d/m/Y H:i');
		$directPaymentRequest['order']['currency'] 			= $directPaymentRequest['payment']['currency'];
		
		// ORDER DETAILS
		foreach($cart->getProducts() as $productDetail) {
			if(round($productDetail['price']*100) > 0)
			{
				$item = array();
				$item['ref'] 				= $productDetail['reference'];
				$item['price'] 				= round($productDetail['price']*100);
				$item['quantity'] 			= $productDetail['cart_quantity'];
				$item['comment'] 			= $productDetail['name'];
				$this->paylineSDK->setItem($item);
			}
		}
		
		// PRIVATE DATA
		$privateData0 = array();
		$privateData0['key'] 					= 'idOrder';
		$privateData0['value'] 					= $newOrderId;
		$this->paylineSDK->setPrivate($privateData0);
		$privateData1 = array();
		$privateData1['key'] 					= 'idCart';
		$privateData1['value'] 					= $cart->id;
		$this->paylineSDK->setPrivate($privateData1);
		$privateData2 = array();
		$privateData2['key'] 					= 'idCust';
		$privateData2['value'] 					= $cust->id;
		$this->paylineSDK->setPrivate($privateData2);
		
		$directPaymentRequest['contracts'] = array($paylineVars['contractNumber']);
			
		// CARD DATA
		$directPaymentRequest['card']['cardHolder'] = $paylineVars['holder'];
		$directPaymentRequest['card']['type'] 		= 'VISA';
		$directPaymentRequest['card']['number'] 	= $paylineVars['cardNumber'];
		if(strlen($paylineVars['monthExpire']) == 1)
			$month = '0'.$paylineVars['monthExpire'];
		else
			$month = $paylineVars['monthExpire'];
		
		$directPaymentRequest['card']['expirationDate'] 		= $month.substr($paylineVars['yearExpire'], -2);
		$directPaymentRequest['card']['cvx'] 		= $paylineVars['crypto'];
		
		// BUYER
		$directPaymentRequest['buyer']['lastName'] 		= $cust->lastname;
		$directPaymentRequest['buyer']['firstName'] 	= $cust->firstname;
		$directPaymentRequest['buyer']['email'] 		= $cust->email;
		
		$directPaymentRequest['buyer']['walletId']	= "";
		
		// ADDRESS
		$directPaymentRequest['address']['name'] 	= $DataAddressDelivery->alias;
		$directPaymentRequest['address']['street1'] 	= $DataAddressDelivery->address1;
		$directPaymentRequest['address']['street2'] 	= $DataAddressDelivery->address2;
		$directPaymentRequest['address']['cityName'] = $DataAddressDelivery->city;
		$directPaymentRequest['address']['zipCode'] 	= $DataAddressDelivery->postcode;
		$directPaymentRequest['address']['country'] 	= $DataAddressDelivery->country;
		$forbidenCars = array(' ','.','(',')','-');
		$directPaymentRequest['address']['phone'] 	= str_replace($forbidenCars,'',$DataAddressDelivery->phone);
		
		// URLs
		$this->paylineSDK->returnURL = Configuration::get('PAYLINE_RETURN_URL');
		$this->paylineSDK->cancelURL = Configuration::get('PAYLINE_CANCEL_URL');
		$this->paylineSDK->notificationURL = Configuration::get('PAYLINE_NOTIFICATION_URL');
		
		// Customization
		$this->paylineSDK->customPaymentPageCode = '';
		$this->paylineSDK->customPaymentTemplateURL = '';
		
		$result = $this->paylineSDK->doAuthorization($directPaymentRequest);
		
		$vars = array();
		if(isset($result) && $result['result']['code'] == '00000'){
			$message = $this->getL('Transaction Payline : ').$result['transaction']['id'];
			$status = _PS_OS_PAYMENT_;
			$vars['transaction_id'] = $result['transaction']['id'];
			$vars['contract_number'] = $paylineVars['contractNumber'];
			$vars['mode'] = 'CPT';
			$vars['action'] = Configuration::get('PAYLINE_DIRECT_ACTION');
			$vars['amount'] = $orderTotal;
			$vars['currency'] = $currency->iso_code_num;
			$vars['by'] = 'directPayment';
			$err=false;
		}else{
			$message = 'Direct payment error (code '.$result['result']['code'].') -> '.$result['result']['longMessage'];
			$status = _PS_OS_ERROR_;
			$err=true;
		}
		$this->validateOrder($cart->id , $status, $cart->getOrderTotal() , $this->displayName, $message.' (direct)',$vars,'','',$cust->secure_key);
		$id_order = intval(Order::getOrderByCartId($cart->id));
		$order = new Order( $id_order );
		$redirectLink = __PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart
		.'&id_module='.$this->id
		.'&key='.$order->secure_key;
		if($err){
			$redirectLink .= '&error='.$err;
		}
		Tools::redirectLink($redirectLink);
		exit;
	}
	
	public function walletPayment($cardInd, $type){
		global $cart, $cookie;
		$this->initPaylineSDK();
		
		$address 				= new Address($cart->id_address_invoice);
		$cust 					= new Customer(intval($cookie->id_customer));
		$pays 					= new Country($address->id_country,$cookie->id_lang);
		$DataAddressDelivery 	= new Address($cart->id_address_delivery);
		
		$currency 	 	 = new Currency(intval($cart->id_currency));
		
		$orderTotalWhitoutTaxes			= round($cart->getOrderTotal(false)*100);
		$orderTotal						= round($cart->getOrderTotal()*100);
		$taxes							= $orderTotal-$orderTotalWhitoutTaxes;
		
		$doImmediateWalletPaymentRequest = array();
		
		$contractNumber = $this->getPaylineContractByCard($type);
		
		// PAYMENT
		$doImmediateWalletPaymentRequest['payment']['amount'] 			= $orderTotal;
		$doImmediateWalletPaymentRequest['payment']['currency'] 		= $currency->iso_code_num;
		$doImmediateWalletPaymentRequest['payment']['contractNumber']	= $contractNumber['contract'];
		$doImmediateWalletPaymentRequest['payment']['mode']				= Configuration::get('PAYLINE_WEB_CASH_MODE');
		$doImmediateWalletPaymentRequest['payment']['action']			= Configuration::get('PAYLINE_WEB_CASH_ACTION');
		
		// ORDER
		$doImmediateWalletPaymentRequest['order']['ref'] 				= $this->l('My cart').' [num '.$cart->id.']';
		$doImmediateWalletPaymentRequest['order']['country'] 			= $pays->iso_code;
		$doImmediateWalletPaymentRequest['order']['taxes'] 				= $taxes;
		$doImmediateWalletPaymentRequest['order']['amount'] 			= $orderTotalWhitoutTaxes;
		$doImmediateWalletPaymentRequest['order']['date'] 				= date('d/m/Y H:i');
		$doImmediateWalletPaymentRequest['order']['currency'] 			= $doImmediateWalletPaymentRequest['payment']['currency'];
		
		// ORDER DETAILS
		foreach($cart->getProducts() as $productDetail) {
			if(round($productDetail['price']*100) > 0)
			{
				$item = array();
				$item['ref'] 				= $productDetail['reference'];
				$item['price'] 				= round($productDetail['price']*100);
				$item['quantity'] 			= $productDetail['cart_quantity'];
				$item['comment'] 			= $productDetail['name'];
				$this->paylineSDK->setItem($item);
			}
		}
		
		// PRIVATE DATA
		$privateData0 = array();
		$privateData0['key'] 					= 'idOrder';
		$privateData0['value'] 					= $cart->id;
		$this->paylineSDK->setPrivate($privateData0);
		$privateData1 = array();
		$privateData1['key'] 					= 'idCart';
		$privateData1['value'] 					= $cart->id;
		$this->paylineSDK->setPrivate($privateData1);
		$privateData2 = array();
		$privateData2['key'] 					= 'idCust';
		$privateData2['value'] 					= $cust->id;
		$this->paylineSDK->setPrivate($privateData2);
		
		// WALLET ID
		$doImmediateWalletPaymentRequest['walletId'] = $this->getOrGenWalletId($cust->id);
		
		// CARDIND
		$doImmediateWalletPaymentRequest['cardInd'] =  $cardInd;
		
		$result = $this->paylineSDK->doImmediateWalletPayment($doImmediateWalletPaymentRequest);
		
		$vars = array();
		if(isset($result) && $result['result']['code'] == '00000'){
			$message = $this->getL('Transaction Payline : ').$result['transaction']['id'];
			$status = _PS_OS_PAYMENT_;
			$vars['transaction_id'] = $result['transaction']['id'];
			$vars['contract_number'] = $contractNumber['contract'];
			$vars['mode'] = Configuration::get('PAYLINE_WEB_CASH_MODE');
			$vars['action'] = Configuration::get('PAYLINE_WEB_CASH_ACTION');
			$vars['amount'] = $orderTotal;
			$vars['currency'] = $currency->iso_code_num;
			$vars['by'] = 'walletPayment';
			$err=false;
		}else{
			$message = 'Wallet payment error (code '.$result['result']['code'].') -> '.$result['result']['longMessage'];
			$status = _PS_OS_ERROR_;
			$err=true;
		}
		$this->validateOrder($cart->id , $status, $cart->getOrderTotal() , $this->displayName, $message.' (wallet)',$vars,'','',$cust->secure_key);
		$id_order = intval(Order::getOrderByCartId($cart->id));
		$order = new Order( $id_order );
		$redirectLink = __PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart
		.'&id_module='.$this->id
		.'&key='.$order->secure_key;
		if($err){
			$redirectLink .= '&error='.$err;
		}
		Tools::redirectLink($redirectLink);
		exit;
	}
	
	public function createWallet(){
		global $cookie;
		$this->initPaylineSDK();
		
		$cust 					= new Customer((int)($cookie->id_customer));
		$addresses				= $cust->getAddresses((int)$cookie->id_lang);
		if(sizeof($addresses))
			$DataAddressDelivery = new Address($addresses[0]['id_address']);
			
		$pays 					= new Country($DataAddressDelivery->id_country);
		
		
		$createWebWalletRequest = array();
		
		// PRIVATE DATA
		$privateData2 = array();
		$privateData2['key'] 					= 'idCust';
		$privateData2['value'] 					= $cust->id;
		$this->paylineSDK->setPrivate($privateData2);
		
		// WALLET ID
		$createWebWalletRequest['buyer']['walletId'] = $this->getOrGenWalletId($cust->id);
		
		$createWebWalletRequest['updatePersonalDetails']= Configuration::get('PAYLINE_WALLET_PERSONNAL_DATA');
		
		$createWebWalletRequest['contractNumber']= Configuration::get('PAYLINE_CONTRACT_NUMBER');
		//SELECTED CONTRACT LIST
		//We retrieve all contract list
		$contracts = $this->getPaylineContracts();
		
		$cardAuthorizeByWallet = explode(',',Configuration::get('PAYLINE_AUTORIZE_WALLET_CARD'));
		
		if($contracts)
		{
			$cards = array();
			foreach($contracts as $contract)
			{
				if(in_array($contract['type'],$cardAuthorizeByWallet) AND !in_array($contract['contract'],$cards))
					$cards[] = $contract['contract'];
			}
		}
		
		if(sizeof($cards)) {
			$createWebWalletRequest['contracts'] = $cards;
		}
			
		// BUYER
		$createWebWalletRequest['buyer']['lastName'] 	= $cust->lastname;
		$createWebWalletRequest['buyer']['firstName'] 	= $cust->firstname;
		$createWebWalletRequest['buyer']['email'] 		= $cust->email;
		
		// ADDRESS
		$createWebWalletRequest['address']['name'] 	= $DataAddressDelivery->alias;
		$createWebWalletRequest['address']['street1'] 	= $DataAddressDelivery->address1;
		$createWebWalletRequest['address']['street2'] 	= $DataAddressDelivery->address2;
		$createWebWalletRequest['address']['cityName'] = $DataAddressDelivery->city;
		$createWebWalletRequest['address']['zipCode'] 	= $DataAddressDelivery->postcode;
		$createWebWalletRequest['address']['country'] 	= $DataAddressDelivery->country;
		$forbidenCars = array(' ','.','(',')','-');
		$createWebWalletRequest['address']['phone'] 	= str_replace($forbidenCars,'',$DataAddressDelivery->phone);
		
		// URLs
		$this->paylineSDK->returnURL = Configuration::get('PAYLINE_RETURN_URL').'?walletInterface=true';
		$this->paylineSDK->cancelURL = Configuration::get('PAYLINE_CANCEL_URL').'?walletInterface=true';
		$this->paylineSDK->notificationURL = Configuration::get('PAYLINE_NOTIFICATION_URL').'?walletInterface=true';
		
		// Customization
		$this->paylineSDK->customPaymentPageCode = Configuration::get('PAYLINE_WALLET_CUSTOM_CODE');
		$this->paylineSDK->customPaymentTemplateURL = '';
		$result = $this->paylineSDK->createWebWallet($createWebWalletRequest);
		
		if(isset($result) && $result['result']['code'] == '00000') {
			return $result['redirectURL'];
		}
		else
			return false;
	}
	
	public function updateWallet($cardInd){
		global $cookie;
		$this->initPaylineSDK();
		
		$cust 					= new Customer((int)($cookie->id_customer));
		$addresses				= $cust->getAddresses((int)$cookie->id_lang);
		if(sizeof($addresses))
			$DataAddressDelivery = new Address($addresses[0]['id_address']);
			
		$pays 					= new Country($DataAddressDelivery->id_country);
		
		
		$updateWebWalletRequest = array();
		
		// PRIVATE DATA
		$privateData2 = array();
		$privateData2['key'] 					= 'idCust';
		$privateData2['value'] 					= $cust->id;
		$this->paylineSDK->setPrivate($privateData2);
		
		// WALLET ID
		$updateWebWalletRequest['walletId'] = $this->getOrGenWalletId($cust->id);
		
		$updateWebWalletRequest['updatePersonalDetails']= Configuration::get('PAYLINE_WALLET_PERSONNAL_DATA');
		$updateWebWalletRequest['updatePaymentDetails']= Configuration::get('PAYLINE_WALLET_PAYMENT_DATA');
		$updateWebWalletRequest['contractNumber']= Configuration::get('PAYLINE_CONTRACT_NUMBER');
		//SELECTED CONTRACT LIST
		//We retrieve all contract list
		$contracts = $this->getPaylineContracts();
		
		$cardAuthorizeByWallet = explode(',',Configuration::get('PAYLINE_AUTORIZE_WALLET_CARD'));
		
		if($contracts)
		{
			$cards = array();
			foreach($contracts as $contract)
			{
				if(in_array($contract['type'],$cardAuthorizeByWallet) AND !in_array($contract['contract'],$cards))
					$cards[] = $contract['contract'];
			}
		}
		
		if(sizeof($cards)) {
			$updateWebWalletRequest['contracts'] = $cards;
		}
		
		$updateWebWalletRequest['cardInd'] = $cardInd;
		
		// BUYER
		$updateWebWalletRequest['buyer']['lastName'] 	= $cust->lastname;
		$updateWebWalletRequest['buyer']['firstName'] 	= $cust->firstname;
		$updateWebWalletRequest['buyer']['email'] 		= $cust->email;
		
		// ADDRESS
		$updateWebWalletRequest['address']['name'] 	= $DataAddressDelivery->alias;
		$updateWebWalletRequest['address']['street1'] 	= $DataAddressDelivery->address1;
		$updateWebWalletRequest['address']['street2'] 	= $DataAddressDelivery->address2;
		$updateWebWalletRequest['address']['cityName'] = $DataAddressDelivery->city;
		$updateWebWalletRequest['address']['zipCode'] 	= $DataAddressDelivery->postcode;
		$updateWebWalletRequest['address']['country'] 	= $DataAddressDelivery->country;
		$forbidenCars = array(' ','.','(',')','-');
		$updateWebWalletRequest['address']['phone'] 	= str_replace($forbidenCars,'',$DataAddressDelivery->phone);
		
		// URLs
		$this->paylineSDK->returnURL = Configuration::get('PAYLINE_RETURN_URL').'?walletInterface=true';
		$this->paylineSDK->cancelURL = Configuration::get('PAYLINE_CANCEL_URL').'?walletInterface=true';
		$this->paylineSDK->notificationURL = Configuration::get('PAYLINE_NOTIFICATION_URL').'?walletInterface=true';
		
		// Customization
		$this->paylineSDK->customPaymentPageCode = Configuration::get('PAYLINE_WALLET_CUSTOM_CODE');
		$this->paylineSDK->customPaymentTemplateURL = '';
		$result = $this->paylineSDK->updateWebWallet($updateWebWalletRequest);
		
		if(isset($result) && $result['result']['code'] == '00000') {
			return $result['redirectURL'];
		}
		else
			return false;
	}
	
	public function deleteCard($cardInd)
	{
		global $cookie,$smarty;
	
		$this->initPaylineSDK();
		
		$cust 					= new Customer((int)($cookie->id_customer));
		
		$deleteWebWalletRequest['contractNumber']= Configuration::get('PAYLINE_CONTRACT_NUMBER');
		$this->paylineSDK->walletIdList = array($this->getOrGenWalletId($cust->id));
		$deleteWebWalletRequest['cardInd'] = $cardInd;
		
		$result = $this->paylineSDK->disableWallet($deleteWebWalletRequest);
		
		if(isset($result) && $result['result']['code'] != '02500')
			return false;
			
		return true;
	}
	
	public function deleteWallet()
	{
		global $cookie,$smarty;
	
		$this->initPaylineSDK();
		
		$cust 					= new Customer((int)($cookie->id_customer));
		
		$deleteWebWalletRequest['contractNumber']= Configuration::get('PAYLINE_CONTRACT_NUMBER');
		$this->paylineSDK->walletIdList = array($this->getOrGenWalletId($cust->id));
		$deleteWebWalletRequest['cardInd'] = '';
		
		$result = $this->paylineSDK->disableWallet($deleteWebWalletRequest);
		
		if(isset($result) && $result['result']['code'] != '02500')
			return false;
			
		return true;
	}
	
	public function getOrGenWalletId($idCust){
		$walletId = $this->getWalletId($idCust);
		if(!$walletId){
			$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZ";
			$salt = '';
			for ($p = 0; $p < 5; $p++) {
				$salt .= $characters[mt_rand(0, strlen($characters)-1)];
			}
			$walletId = $idCust.'_'.date('YmdHis').'_'.$salt;
			
			$this->setWalletId($idCust, $walletId);
		}
		return $walletId;
	}  
	
	/*****************************************************************************************
	** VALIDATE ORDER FUNCTION
	******************************************************************************************/
	function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = false, $secure_key = NULL)
    {
        if (!$this->active)
            return ;
        parent::validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod, $message, $extraVars, $currency_special, $dont_touch_amount, $secure_key);
    	
    	if (array_key_exists('transaction_id', $extraVars))
    		$this->_saveTransaction($id_cart,$extraVars['transaction_id'], $extraVars['contract_number'], $extraVars['action'],$extraVars['mode'],$extraVars['amount'],$extraVars['currency'], $extraVars['by']);
    }
    
	private function _saveTransaction($id_cart, $transaction_id, $contract_number, $action=0, $mode, $amount, $currency, $by)
	{
		$cart = new Cart((int)($id_cart));
		if (Validate::isLoadedObject($cart) AND $cart->OrderExists())
		{
			$id_order = Db::getInstance()->getValue('
			SELECT `id_order` 
			FROM `'._DB_PREFIX_.'orders` 
			WHERE `id_cart` = '.(int)$cart->id);
			
			if($action == 100)
				$action = 'authorization';
			else
				$action = 'capture';
				
			if($mode != 'NX')
				Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'payline_order` (`id_order`, `id_transaction`, `contract_number`, `payment_status`, `mode`, `amount`, `currency`, `payment_by`) 
				VALUES ('.(int)$id_order.', \''.$transaction_id.'\', \''.$contract_number.'\', \''.$action.'\', \''.$mode.'\', '.$amount.', '.$currency.', \''.$by.'\')');
		}
	}
}
?>
