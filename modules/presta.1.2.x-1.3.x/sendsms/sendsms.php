<?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

include_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');

class sendsms extends Module
{
	private $_tabsArray;

	function __construct()
	{
		$this->name = 'sendsms';
		$this->tab = 'Yann Bonnaillie';
		$this->version = '1.13';
		$this->displayName = $this->l('SendSMS');

		$config = Configuration::getMultiple(array('SENDSMS_EMAIL', 'SENDSMS_PASSWORD', 'SENDSMS_KEY', 'SENDSMS_SENDER', 'SENDSMS_ADMIN_PHONE_NUMBER'));
		if (!isset($config['SENDSMS_EMAIL']) || !isset($config['SENDSMS_PASSWORD']) || !isset($config['SENDSMS_KEY']) || !isset($config['SENDSMS_SENDER']) || !isset($config['SENDSMS_ADMIN_PHONE_NUMBER']))
			$this->warning = $this->l('You have not yet set your SendSMS parameters. Please click on "SMS" tab.');
		$this->description = $this->l('Module to send SMS on differents events');
		$this->confirmUninstall = $this->l('Are you sure you want to delete this module ?');

		$this->_tabsArray = array(
			'sendsmsMessagesTab'=> array(
										Language::getIdByIso('fr') => 'Gestion des messages',
										Language::getIdByIso('en') => 'Messages management',
										Language::getIdByIso('es') => 'Gestión de mensajes',
										'default'=>'Messages management'
									),
			'sendsmsLogsTab' 	=> array(
										Language::getIdByIso('fr') => 'Historique des envois',
										Language::getIdByIso('en') => 'SMS history',
										Language::getIdByIso('es') => 'Histórico de Mensajes',
										'default'=>'SMS history'
								),
			'sendsmsSendTab' 	=> array(
										Language::getIdByIso('fr') => 'Envoyer un SMS',
										Language::getIdByIso('en') => 'Send a SMS',
										Language::getIdByIso('es') => 'Enviar un SMS',
										'default'=>'Send a SMS'
								),
			'sendsmsStatsTab' 	=> array(
										Language::getIdByIso('fr') => 'Statistiques',
										Language::getIdByIso('en') => 'Statistics',
										Language::getIdByIso('es') => 'Estadísticas',
										'default'=>'Statistics'
									),
		);

		parent::__construct();
	}

	public function install()
	{
		if (!parent::install() || !$this->_installDatabase() || !$this->_installTabs($tabsArray) || !$this->_installConfig() || !$this->_installHooks() || !$this->_installFiles())
			return false;
		return true;
	}

	public function uninstall()
	{
		if(!parent::uninstall() || !$this->_uninstallDatabase() || !$this->_uninstallTabs($tabsArray) || !$this->_uninstallConfig() || !$this->_uninstallHooks() || !$this->_uninstallFiles())
			return false;
		return true;
	}

	private function _installDatabase() {
		// Add log table to database
		Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'sendsms_logs` (
			  `id_sendsms_logs` int(10) unsigned NOT NULL auto_increment,
			  `id_customer` int(10) unsigned default NULL,
			  `recipient` varchar(100) NOT NULL,
			  `phone` varchar(16) NOT NULL,
			  `event` varchar(64) NOT NULL,
			  `message` text NOT NULL,
			  `nb_consumed` tinyint(1) unsigned NOT NULL default \'0\',
			  `credit` double(5,3) default NULL,
			  `paid_by_customer` tinyint(1) unsigned NOT NULL default \'0\',
			  `simulation` tinyint(1) unsigned NOT NULL default \'0\',
			  `status` tinyint(1) NOT NULL default \'0\',
			  `ticket` varchar(255) default NULL,
			  `error` varchar(255) default NULL,
			  `date_add` datetime NOT NULL,
			  PRIMARY KEY  (`id_sendsms_logs`)
			) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;'
		);

		// Add phone prefix to database
		Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'sendsms_phone_prefix` (
			`iso_code` varchar(3) NOT NULL,
			`prefix` int(10) unsigned default NULL,
			PRIMARY KEY  (`iso_code`)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;'
		);
		Db::getInstance()->Execute("
			INSERT INTO `" . _DB_PREFIX_ . "sendsms_phone_prefix` (`iso_code`, `prefix`) VALUES
				('AD', 376),('AE', 971),('AF', 93),('AG', 1268),('AI', 1264),('AL', 355),('AM', 374),('AN', 599),('AO', 244),
				('AQ', 672),('AR', 54),('AS', 1684),('AT', 43),('AU', 61),('AW', 297),('AX', NULL),('AZ', 994),('BA', 387),
				('BB', 1246),('BD', 880),('BE', 32),('BF', 226),('BG', 359),('BH', 973),('BI', 257),('BJ', 229),('BL', 590),('BM', 1441),
				('BN', 673),('BO', 591),('BR', 55),('BS', 1242),('BT', 975),('BV', NULL),('BW', 267),('BY', 375),('BZ', 501),
				('CA', 1),('CC', 61),('CD', 242),('CF', 236),('CG', 243),('CH', 41),('CI', 225),('CK', 682),('CL', 56),('CM', 237),
				('CN', 86),('CO', 57),('CR', 506),('CU', 53),('CV', 238),('CX', 61),('CY', 357),('CZ', 420),('DE', 49),('DJ', 253),
				('DK', 45),('DM', 1767),('DO', 1809),('DZ', 213),('EC', 593),('EE', 372),('EG', 20),('EH', NULL),('ER', 291),('ES', 34),
				('ET', 251),('FI', 358),('FJ', 679),('FK', 500),('FM', 691),('FO', 298),('FR', 33),('GA', 241),('GB', 44),('GD', 1473),
				('GE', 995),('GF', 594),('GG', NULL),('GH', 233),('GI', 350),('GL', 299),('GM', 220),('GN', 224),('GP', 590),('GQ', 240),
				('GR', 30),('GS', NULL),('GT', 502),('GU', 1671),('GW', 245),('GY', 592),('HK', 852),('HM', NULL),('HN', 504),('HR', 385),
				('HT', 509),('HU', 36),('ID', 62),('IE', 353),('IL', 972),('IM', 44),('IN', 91),('IO', 1284),('IQ', 964),('IR', 98),
				('IS', 354),('IT', 39),('JE', 44),('JM', 1876),('JO', 962),('JP', 81),('KE', 254),('KG', 996),('KH', 855),('KI', 686),
				('KM', 269),('KN', 1869),('KP', 850),('KR', 82),('KW', 965),('KY', 1345),('KZ', 7),('LA', 856),('LB', 961),('LC', 1758),
				('LI', 423),('LK', 94),('LR', 231),('LS', 266),('LT', 370),('LU', 352),('LV', 371),('LY', 218),('MA', 212),('MC', 377),
				('MD', 373),('ME', 382),('MF', 1599),('MG', 261),('MH', 692),('MK', 389),('ML', 223),('MM', 95),('MN', 976),('MO', 853),
				('MP', 1670),('MQ', 596),('MR', 222),('MS', 1664),('MT', 356),('MU', 230),('MV', 960),('MW', 265),('MX', 52),('MY', 60),
				('MZ', 258),('NA', 264),('NC', 687),('NE', 227),('NF', 672),('NG', 234),('NI', 505),('NL', 31),('NO', 47),('NP', 977),
				('NR', 674),('NU', 683),('NZ', 64),('OM', 968),('PA', 507),('PE', 51),('PF', 689),('PG', 675),('PH', 63),('PK', 92),
				('PL', 48),('PM', 508),('PN', 870),('PR', 1),('PS', NULL),('PT', 351),('PW', 680),('PY', 595),('QA', 974),('RE', 262),
				('RO', 40),('RS', 381),('RU', 7),('RW', 250),('SA', 966),('SB', 677),('SC', 248),('SD', 249),('SE', 46),('SG', 65),
				('SI', 386),('SJ', NULL),('SK', 421),('SL', 232),('SM', 378),('SN', 221),('SO', 252),('SR', 597),('ST', 239),('SV', 503),
				('SY', 963),('SZ', 268),('TC', 1649),('TD', 235),('TF', NULL),('TG', 228),('TH', 66),('TJ', 992),('TK', 690),('TL', 670),
				('TM', 993),('TN', 216),('TO', 676),('TR', 90),('TT', 1868),('TV', 688),('TW', 886),('TZ', 255),('UA', 380),('UG', 256),
				('US', 1),('UY', 598),('UZ', 998),('VA', 379),('VC', 1784),('VE', 58),('VG', 1284),('VI', 1340),('VN', 84),('VU', 678),
				('WF', 681),('WS', 685),('YE', 967),('YT', 262),('ZA', 27),('ZM', 260),('ZW', 263);"
		);
		return true;
	}

	private function _uninstallDatabase() {
		// remove phone prefix from database
		Db::getInstance()->ExecuteS('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'sendsms_phone_prefix`');
		//Db::getInstance()->ExecuteS('DROP TABLE `' . _DB_PREFIX_ . 'sendsms_logs`');
		return true;
	}

	private function _installTabs()
	{
		@copy(_PS_MODULE_DIR_.$this->name.'/sendsmsTab.gif', _PS_IMG_DIR_.'t/sendsmsTab.gif');
		$tab = new Tab();
		foreach (Language::getLanguages() as $language) {
	      $tab->name[$language['id_lang']] = 'SMS';
	    }
		$tab->class_name = 'sendsmsTab';
		$tab->module = $this->name;
		$tab->id_parent = 0;
		if(!$tab->save()) {
			return false;
		} else {
			$idTab = Tab::getIdFromClassName('sendsmsTab');

			foreach($this->_tabsArray as $tabKey => $names) {
				@copy(_PS_MODULE_DIR_.$this->name.'/'.$tabKey.'.gif', _PS_IMG_DIR_.'t/'.$tabKey.'.gif');
				$tab = new Tab();
				foreach (Language::getLanguages() as $language) {
					if (isset($names[$language['id_lang']]))
			      		$tab->name[$language['id_lang']] = $names[$language['id_lang']];
			      	else
			      		$tab->name[$language['id_lang']] = $names['default'];
			    }
				$tab->class_name = $tabKey;
				$tab->module = $this->name;
				$tab->id_parent = $idTab;
				if(!$tab->save()) {
					return false;
				}
			}
		}
		return true;
	}

	private function _uninstallTabs()
	{
		foreach($this->_tabsArray as $tabKey => $names) {
			$idTab = Tab::getIdFromClassName($tabKey);
			if($idTab != 0) {
				$tab = new Tab($idTab);
				$tab->delete();
				@unlink(_PS_IMG_DIR_.'t/'.$tabKey.'.gif');
			}
		}

		$idTab = Tab::getIdFromClassName('sendsmsTab');
		if($idTab != 0) {
			$tab = new Tab($idTab);
			$tab->delete();
		}
		return true;
	}

	private function _installConfig()
	{
		Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'configuration_lang`
			WHERE `id_configuration` NOT IN (SELECT `id_configuration` from `'._DB_PREFIX_.'configuration`)');
		Configuration::updateValue('SENDSMS_SIMULATION', '1');
		Configuration::updateValue('SENDSMS_FREEOPTION', '1');
		Configuration::updateValue('SENDSMS_PUT_IN_CART', '0');
		Configuration::updateValue('SENDSMS_ALERT_LEVEL', '10');
		Configuration::updateValue('SENDSMS_ALERT_SENT', '0');
		return true;
	}

	private function _uninstallConfig()
	{
		Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'configuration`
			WHERE `name` like \'SENDSMS_%\'');
		Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'configuration_lang`
			WHERE `id_configuration` NOT IN (SELECT `id_configuration` from `'._DB_PREFIX_.'configuration`)');
		return true;
	}

	private function _installHooks()
	{
		Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'hook` (id_hook,name,title,description,position) VALUES
		(NULL ,"sendsmsContactForm","Post submit of contact form","This hook is called when a message is sent from contact form","0"),
		(NULL ,"sendsmsCheckCartForSms","Check if SMS already in cart","Set the quantity to 1 in cart for SMS notification","0"),
		(NULL ,"sendsmsCustomerChoice","Display SMS notification choice on order carrier page","This hook is called when displayingorder carrier page","0"),
		(NULL ,"sendsmsProcessCustomerChoice","Process SMS notification choice on order carrier page","This hook is called when saving carrier page","0"),
		(NULL ,"sendsmsShippingNumber","Post update shipping number on order","This hook is called when a shipping number is updated on an order","0"),
		(NULL ,"sendsmsAdminAlert","Send SMS when account is almost empty","This hook is called when Send SMS account is almost empty","0"),
		(NULL ,"sendsmsCustomerAlert","Send SMS when product is available","This hook is called by mailalert module","0"),
		(NULL ,"sendsmsLostPassword","Send SMS when customer has lost his password","This hook is called when sending a new password to a customer","0"),
		(NULL ,"sendsmsDailyReport","Send SMS for daily report","This hook is called by the sendsms cron","0")' .
		(!Hook::get('postUpdateOrderStatus') ? ',(NULL ,"postUpdateOrderStatus","Post update of order status","Post update of order status","0")' : ''));

		if (!$this->registerHook('createAccount') || !$this->registerHook('newOrder') || !$this->registerHook('updateQuantity') || !$this->registerHook('orderReturn')
		 || !$this->registerHook('postUpdateOrderStatus') || !$this->registerHook('sendsmsShippingNumber') || !$this->registerHook('sendsmsContactForm')
		 || !$this->registerHook('sendsmsCheckCartForSms') || !$this->registerHook('sendsmsCustomerChoice') || !$this->registerHook('sendsmsProcessCustomerChoice')
		 || !$this->registerHook('sendsmsAdminAlert') || !$this->registerHook('sendsmsCustomerAlert') || !$this->registerHook('sendsmsDailyReport')
		  || !$this->registerHook('sendsmsLostPassword'))
		{
			return false;
		}
		return true;
	}

	private function _uninstallHooks()
	{
		return Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'hook`
			WHERE `name` like \'sendsms%\'');
	}

	private function _installFiles() {
		$this->_modifyFile('./tabs/AdminOrders.php', 'sendsmsShippingNumber', "Mail::Send(intval(\$order->id_lang), 'in_transit', ((is_array(\$_LANGMAIL) AND key_exists(\$subject, \$_LANGMAIL)) ? \$_LANGMAIL[\$subject] : \$subject), \$templateVars, \$customer->email, \$customer->firstname.' '.\$customer->lastname);", "Mail::Send(intval(\$order->id_lang), 'in_transit', ((is_array(\$_LANGMAIL) AND key_exists(\$subject, \$_LANGMAIL)) ? \$_LANGMAIL[\$subject] : \$subject), \$templateVars, \$customer->email, \$customer->firstname.' '.\$customer->lastname);\nModule::hookExec('sendsmsShippingNumber', array('customer' => \$customer, 'order' => \$order, 'carrier' => \$carrier));");
		$this->_modifyFile('../contact-form.php', 'sendsmsContactForm', "\$smarty->assign('confirmation', 1);", "\$smarty->assign('confirmation', 1);\nif (\$smarty->get_template_vars('confirmation') == 1) { Module::hookExec('sendsmsContactForm', array('contact' => \$contact, 'customer' => \$customer, 'from' => \$from, 'message' => eregi_replace('<br[[:space:]]*/?[[:space:]]*>',chr(13).chr(10),\$message))); }");
		$this->_modifyFile('../password.php', 'sendsmsLostPassword', "\$smarty->assign(array('confirmation' => 1, 'email' => \$customer->email));", "\$smarty->assign(array('confirmation' => 1, 'email' => \$customer->email));\nModule::hookExec('sendsmsLostPassword', array('customer' => \$customer, 'password' => \$password));");
		$this->_modifyFile('../themes/' . _THEME_NAME_ . '/order-carrier.tpl', 'HOOK_SENDSMS_CUSTOMER_CHOICE', "{if \$giftAllowed}", "{\$HOOK_SENDSMS_CUSTOMER_CHOICE}\n\n{if \$giftAllowed}");
		$this->_modifyFile('../modules/mailalerts/mailalerts.php', 'sendsmsCustomerAlert', "\$customer_id = \$customer->id;", "\$customer_id = \$customer->id;\nModule::hookExec('sendsmsCustomerAlert', array('customer' => \$customer, 'product' => \$product));");

		// fichier order.php avec plusieurs remplacements
		$order[0] = "
		if (Module::isInstalled('sendsms')) {
			if (!Module::hookExec('sendsmsCustomerChoice', array('submit' => true, 'customerChoice' => \$_POST['sendsms']))) {
				require_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');
				\$manager = new sendsmsManager();
			    \$errors[] = Tools::displayError(\$manager->getLabel('customer_phone_error', \$cart->id_lang));
			}
		}\n\n\$cart->recyclable = (isset(\$_POST['recyclable']) AND !empty(\$_POST['recyclable'])) ? 1 : 0;";
		$order[1] = "'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => \$address)),\n'HOOK_SENDSMS_CUSTOMER_CHOICE' => Module::hookExec('sendsmsCustomerChoice'),";
		$order[2] = "Module::hookExec('sendsmsCheckCartForSms');\n\nif (file_exists(_PS_SHIP_IMG_DIR_.intval(\$cart->id_carrier).'.jpg'))";

		$this->_modifyFile('../order.php', 'sendsms', array("\$cart->recyclable = (isset(\$_POST['recyclable']) AND !empty(\$_POST['recyclable'])) ? 1 : 0;", "'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => \$address)),", "if (file_exists(_PS_SHIP_IMG_DIR_.intval(\$cart->id_carrier).'.jpg'))"), $order);


		// suppression du cache smarty
		$files = glob('../tools/smarty/compile/*order-carrier.tpl.php');
		if (is_array($files)) {
			foreach($files as $file) {
				@unlink($file);
			}
		}

		return true;
	}

	private function _uninstallFiles() {
		$this->_restoreFile('./tabs/AdminOrders.php', "\nModule::hookExec('sendsmsShippingNumber', array('customer' => \$customer, 'order' => \$order, 'carrier' => \$carrier));");
		$this->_restoreFile('../contact-form.php', "\nif (\$smarty->get_template_vars('confirmation') == 1) { Module::hookExec('sendsmsContactForm', array('contact' => \$contact, 'customer' => \$customer, 'from' => \$from, 'message' => eregi_replace('<br[[:space:]]*/?[[:space:]]*>',chr(13).chr(10),\$message))); }");
		$this->_restoreFile('../password.php', "\nModule::hookExec('sendsmsLostPassword', array('customer' => \$customer, 'password' => \$password));");
		$this->_restoreFile('../themes/' . _THEME_NAME_ . '/order-carrier.tpl', "{\$HOOK_SENDSMS_CUSTOMER_CHOICE}\n\n");
		$this->_restoreFile('../modules/mailalerts/mailalerts.php', "\nModule::hookExec('sendsmsCustomerAlert', array('customer' => \$customer, 'product' => \$product));");

		// fichier order.php avec plusieurs remplacements
		$order[0] = "
		if (Module::isInstalled('sendsms')) {
			if (!Module::hookExec('sendsmsCustomerChoice', array('submit' => true, 'customerChoice' => \$_POST['sendsms']))) {
				require_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');
				\$manager = new sendsmsManager();
			    \$errors[] = Tools::displayError(\$manager->getLabel('customer_phone_error', \$cart->id_lang));
			}
		}\n\n";
		$order[1] = "\n'HOOK_SENDSMS_CUSTOMER_CHOICE' => Module::hookExec('sendsmsCustomerChoice'),";
		$order[2] = "Module::hookExec('sendsmsCheckCartForSms');\n\n";

		$this->_restoreFile('../order.php', $order);

		return true;
	}

	private function _modifyFile($path, $search, $replace1, $replace2) {
		if (file_exists($path)) {
			$fd = fopen($path, 'r');
			$contents = fread($fd, filesize($path));
			if (strpos($contents, $search) === false) {
				$content2 = $contents;
				if (is_array($replace1) && is_array($replace2)) {
					foreach($replace1 as $key => $val1) {
						$contents = str_replace($val1, $replace2[$key], $contents);
					}
				} else
					$contents = str_replace($replace1, $replace2, $contents);
				fclose($fd);
				copy($path, $path . '-savedbysendsms');
				$fd = fopen($path, 'w+');
				fwrite($fd, $contents);
				fclose($fd);
			} else {
				fclose($fd);
			}
		}
	}

	private function _restoreFile($path, $search) {
		if (file_exists($path)) {
			$fd = fopen($path, 'r');
			$contents = fread($fd, filesize($path));
			if (is_array($search)) {
				foreach($search as $val) {
					$contents = str_replace($val, "", $contents);
				}
			} else
				$contents = str_replace($search, "", $contents);

			fclose($fd);
			$fd = fopen($path, 'w+');
			fwrite($fd, $contents);
			fclose($fd);
			@unlink($path . '-savedbysendsms');
		}
	}

	public function hookCreateAccount($params)
	{
		sendsmsManager::send('createAccount', $params);
	}

	public function hookNewOrder($params)
	{
		sendsmsManager::send('newOrder', $params);
	}

	public function hookUpdateQuantity($params)
	{
		sendsmsManager::send('updateQuantity', $params);
	}

	public function hookOrderReturn($params)
	{
		sendsmsManager::send('orderReturn', $params);
	}

	public function hookPostUpdateOrderStatus($params)
	{
		sendsmsManager::send('postUpdateOrderStatus', $params);
	}

	public function hookSendsmsShippingNumber($params)
	{
		sendsmsManager::send('sendsmsShippingNumber', $params);
	}

	public function hookSendsmsContactForm($params)
	{
		sendsmsManager::send('sendsmsContactForm', $params);
	}

	public function hookSendsmsAdminAlert($params)
	{
		sendsmsManager::send('sendsmsAdminAlert', $params);
	}

	public function hookSendsmsCustomerAlert($params)
	{
		sendsmsManager::send('sendsmsCustomerAlert', $params);
	}

	public function hookSendsmsLostPassword($params)
	{
		sendsmsManager::send('sendsmsLostPassword', $params);
	}

	public function hookSendsmsDailyReport($params)
	{
		sendsmsManager::send('sendsmsDailyReport', $params);
	}

	public function hookSendsmsCheckCartForSms($params)
	{
		sendsmsManager::checkCartForSms($params);
	}

	public function hookSendsmsCustomerChoice($params)
	{
		if (!$params['submit']) {
			if (sendsmsManager::displayCustomerChoice($params))
				return $this->display(__FILE__, 'sendsms.tpl');
		} else {
			return sendsmsManager::processCustomerChoice($params);
		}
	}
}
?>