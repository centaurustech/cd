 <?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

include_once(_PS_MODULE_DIR_.'/sendsms/classes/sms.php');
include_once(_PS_MODULE_DIR_.'/sendsms/sendsmsLogs.php');

class sendsmsManager
{
	const __SENDSMS_CUSTOMER__ = 1;
	const __SENDSMS_ADMIN__ = 2;
	const __SENDSMS_BOTH__ = 3;

	private $_module = 'sendsms';
	private $_labels = array();
	private static $_hookName;
	private static $_params;
	private static $_phone = '';
	private static $_email;
	private static $_password;
	private static $_prestaKey;
	private static $_balance = 0;
	private static $_txt = '';
	private static $_recipient;
	private static $_paid_by_customer = 0;
	private static $_simulation = 0;
	private static $_event = '';

	private static $_config = array(
		'sendsmsFree' => 0,
	    'createAccount' => self::__SENDSMS_BOTH__,
	    'sendsmsLostPassword' => self::__SENDSMS_CUSTOMER__,
		'sendsmsCustomerAlert' => self::__SENDSMS_CUSTOMER__,
	    'sendsmsContactForm' => self::__SENDSMS_BOTH__,
	    'newOrder' => self::__SENDSMS_BOTH__,
	    'orderReturn' => self::__SENDSMS_ADMIN__,
	    'sendsmsShippingNumber' => self::__SENDSMS_CUSTOMER__,
	    'updateQuantity' => self::__SENDSMS_ADMIN__,
	    'sendsmsAdminAlert' => self::__SENDSMS_ADMIN__,
	    'sendsmsDailyReport' => self::__SENDSMS_ADMIN__,
	    'postUpdateOrderStatus' => self::__SENDSMS_CUSTOMER__
	);

	public function __construct()
	{
		$this->_labels['sendsmsFree'] = $this->l('Freehand SMS');
	    $this->_labels['newOrder'] = $this->l('On new order');
	    $this->_labels['orderReturn'] = $this->l('When a return request is created by a customer');
	    $this->_labels['createAccount'] = $this->l('On account creation');
	    $this->_labels['updateQuantity'] = $this->l('On out of stock');
	    $this->_labels['sendsmsShippingNumber'] = $this->l('When shipping number is updated');
	    $this->_labels['sendsmsContactForm'] = $this->l('On contact message');
	    $this->_labels['sendsmsAdminAlert'] = $this->l('When account is almost empty');
	    $this->_labels['sendsmsCustomerAlert'] = $this->l('When product is now available and customer asked for being notified');
	    $this->_labels['sendsmsLostPassword'] = $this->l('When a customer has lost his password and want to receive a new one');
	    $this->_labels['sendsmsDailyReport'] = $this->l('Daily report');
	    $this->_labels['postUpdateOrderStatus'] = $this->l('On order status change');
	    $this->_labels['postUpdateOrderStatus_short'] = $this->l('Order');
	    $this->_refreshLabel();
	}

	private function _refreshLabel($lang_id=null) {
	    $this->_labels['customer_phone_error'] = $this->l('You can\'t choose this option, because your mobile phone number is not set in your invoice address.', $lang_id);
	    $this->_labels['newOrder_default_admin'] = $this->l('New order from {firstname} {lastname}, id: {order_id}, payment: {payment}, total: {total_paid} {currency}.', $lang_id);
	    $this->_labels['newOrder_default_customer'] = $this->l('{firstname} {lastname}, we confirm your order {order_id}, which amount is {total_paid} {currency}. Thanks. {shopname}', $lang_id);
	    $this->_labels['orderReturn_default_admin'] = $this->l('Return request ({return_id}) received from customer {customer_id} about order {order_id}. Reason : {message}', $lang_id);
	    $this->_labels['sendsmsShippingNumber_default_customer'] = $this->l('{firstname} {lastname}, your order {order_id} is currently in transit. Your tracking number is {shipping_number}. Thanks. {shopname}', $lang_id);
	    $this->_labels['createAccount_default_admin'] = $this->l('{firstname} {lastname} has just subscribed to {shopname}', $lang_id);
	    $this->_labels['createAccount_default_customer'] = $this->l('{firstname} {lastname}, welcome on {shopname} !', $lang_id);
	    $this->_labels['updateQuantity_default_admin'] = $this->l('This product is almost out of stock, id: {product_id}, ref: {product_ref}, name: {product_name}, qty: {quantity}', $lang_id);
	    $this->_labels['sendsmsContactForm_default_admin'] = $this->l('{from} just sent a message to {contact_name} ({contact_mail}) : {message}', $lang_id);
	    $this->_labels['sendsmsContactForm_default_customer'] = $this->l('Thank you for your message. We will reply to you as soon as possible. {shopname}', $lang_id);
	    $this->_labels['sendsmsAdminAlert_default_admin'] = $this->l('Your SMS account is almost empty. Only {balance} € left.');
	    $this->_labels['sendsmsCustomerAlert_default_customer'] = $this->l('{firstname} {lastname}, {product} is now avalaible on {shopname} ({shopurl})');
	    $this->_labels['sendsmsLostPassword_default_customer'] = $this->l('{firstname} {lastname}, your new password on {shopname} is : {password}. {shopurl}');
	    $this->_labels['sendsmsDailyReport_default_admin'] = $this->l('date: {date}, subscriptions: {subs}, visitors: {visitors}, visits: {visits}, orders: {orders}, sales: {day_sales}, month: {month_sales}');
	    $this->_labels['postUpdateOrderStatus_default_customer'] = $this->l('{firstname} {lastname}, your status order on {shopname} has just changed : {order_state}', $lang_id);
	}

	public function getLabel($key, $lang_id=null)
	{
		$this->_refreshLabel($lang_id);
		return $this->_labels[$key];
	}

	static public function isAccountAvailable($email, $password, $prestaKey)
	{
		if (!empty($email) && !empty($password) && !empty($prestaKey)) {
			$sms = new SMS();
			$sms->setSmsLogin($email);
			$sms->setSmsPassword($password);
			$sms->setPrestaKey($prestaKey);
			$result = $sms->number();
			if (strpos($result, 'KO') !== false) {
				return false;
			} else {
				self::$_email = $email;
				self::$_password = $password;
				self::$_prestaKey = $prestaKey;
				self::$_balance = empty($result) ? 0 : floatval($result);
				$alertLevel = intval(Configuration::get('SENDSMS_ALERT_LEVEL'));
				if ($alertLevel > 0 && self::$_balance > $alertLevel) {
					Configuration::updateValue('SENDSMS_ALERT_SENT', '0');
					// compatibilité 1.2.5
					Configuration::set('SENDSMS_ALERT_SENT', '0');
				}
				return true;
			}
		}
		return false;
	}

	static public function getXmlRss($email, $password, $prestaKey)
	{
		if (!empty($email) && !empty($password) && !empty($prestaKey)) {
			$sms = new SMS();
			$sms->setSmsLogin($email);
			$sms->setSmsPassword($password);
			$sms->setPrestaKey($prestaKey);
			$result = $sms->rss();
			if (strpos($result, 'KO') !== false) {
				return false;
			} else {
				return $result;
			}
		}
		return false;
	}

	static public function getBalance()
	{
		return self::$_balance;
	}

	static public function getConfig()
	{
		foreach(self::$_config as $key => $value) {
			$hook = new Hook();
			$hook_id = $hook->get($key);
			if ($hook_id || $key == 'sendsmsFree')
				$result[$hook_id] = array(0 => $key, 1 => $value);
		}
		return $result;
	}

	static public function send($hookName, $params)
	{
		self::$_hookName = $hookName;
		self::$_params = $params;

		$dest = self::$_config[self::$_hookName];
		if ($dest == self::__SENDSMS_CUSTOMER__ || $dest == self::__SENDSMS_BOTH__) {
			self::_prepareSms();
		}
		if ($dest == self::__SENDSMS_ADMIN__ || $dest == self::__SENDSMS_BOTH__) {
			self::_prepareSms(true);
		}

		if (self::$_event != 'sendsmsAdminAlert') {
			Module::hookExec('sendsmsAdminAlert', null);
		}
	}

	private function _isEverythingValidForSending($keyActive, $keyTxt, $idLang=null, $bAdmin=false)
	{
		if (!empty(self::$_phone) && Configuration::get('SENDSMS_SENDER') && Configuration::get($keyActive) && Configuration::get($keyTxt,$idLang) &&
			self::isAccountAvailable(Configuration::get('SENDSMS_EMAIL'), Configuration::get('SENDSMS_PASSWORD'), Configuration::get('SENDSMS_KEY')) && self::isBalancePositive())
			return true;
		return false;
	}

	static public function isBalancePositive() {
		return self::$_balance > 0;
	}

	private function _prepareSms($bAdmin = false)
	{
		global $cookie;

		$method = '_get' . ucfirst(self::$_hookName) . 'Values';
		if (method_exists(__CLASS__, $method)) {
			$hook = new Hook();
			$hookId = $hook->get(self::$_hookName);
			if ($hookId) {
				self::$_recipient = null;
				self::$_paid_by_customer = 0;
				self::$_event = self::$_hookName;
				$idLang = $bAdmin ? null : $cookie->id_lang;

				switch (self::$_hookName) {
					case 'postUpdateOrderStatus':
						$stateId = self::$_params['newOrderStatus']->id;
						$order = new Order(intval(self::$_params['id_order']));
						$id_lang = $order->id_lang;
						$keyActive = 'SENDSMS_ISACTIVE_' . $hookId . '_' . $stateId;
						$keyTxt = 'SENDSMS_TXT_' . $hookId . '_' . $stateId;
						self::$_event .= '_' . $stateId;
						$values = self::$method(false, false);
						break;
					case 'sendsmsShippingNumber':
						$order = self::$_params['order'];
						$id_lang = $order->id_lang;
						$keyActive = 'SENDSMS_ISACTIVE_' . $hookId;
						$keyTxt = 'SENDSMS_TXT_' . $hookId;
						$values = self::$method(false, false);
						break;
					default :
						$keyActive = ($bAdmin) ? 'SENDSMS_ISACTIVE_' . $hookId . '_ADMIN' : 'SENDSMS_ISACTIVE_' . $hookId;
						$keyTxt = ($bAdmin) ? 'SENDSMS_TXT_' . $hookId . '_ADMIN' : 'SENDSMS_TXT_' . $hookId;
						$values = self::$method(false, $bAdmin);
						break;
				}

				if (is_array($values) && self::_isEverythingValidForSending($keyActive, $keyTxt, $idLang, $bAdmin)) {
					self::$_txt = str_replace(array_keys($values), array_values($values), Configuration::get($keyTxt, $idLang));
					self::_sendSMS();
				}
			}
		}
	}

	private function _sendSMS()
	{
		$sms = new SMS();
		$sms->setSmsLogin(self::$_email);
		$sms->setSmsPassword(self::$_password);
		$sms->setPrestaKey(self::$_prestaKey);
		$sms->setSmsText(self::$_txt);
		$sms->setNums(array(self::$_phone));
		$sms->setType(INSTANTANE);
		$sms->setSender(Configuration::get('SENDSMS_SENDER'));
		$sms->setSimulation(intval(Configuration::get('SENDSMS_SIMULATION')));
		$reponse = $sms->send();
		$result = split('_', $reponse);

		$log = new sendsmsLogs();
		if (isset(self::$_recipient)) {
			if (self::$_event != 'sendsmsFree') {
				$log->id_customer = self::$_recipient->id;
				$log->recipient = self::$_recipient->firstname . ' ' . self::$_recipient->lastname;
			} else {
				$log->recipient = self::$_recipient;
			}
		} else {
			$log->recipient = '--';
		}
		$log->phone = self::$_phone;
		$log->event = self::$_event;
		$log->message = self::$_txt;
		$log->nb_consumed = $result[2];
		$log->credit = ($result[0] == 'KO') ? 0 : $result[3];
		$log->paid_by_customer = self::$_paid_by_customer;
		$log->simulation = intval(Configuration::get('SENDSMS_SIMULATION'));
		$log->status = ($result[0] == 'OK') ? 1 : 0;
		$log->ticket = ($result[0] == 'OK') ? $result[1] : null;
		$log->error = ($result[0] == 'KO') ? $result[1] : null;
		$log->save();

		if ($result[0] == 'OK')
			return true;
		return false;
	}

	static public function sendFreeSMS($phone, $recipient, $txt)
	{
		self::$_phone = $phone;
		self::$_txt = self::replaceForGSM7($txt);
		self::$_event = 'sendsmsFree';
		self::$_paid_by_customer = 0;
		self::$_recipient = $recipient;
		return self::_sendSMS();
	}

	static public function getSmsValuesForTest($hookName)
	{
		$values = array();
		$method = '_get' . ucfirst($hookName) . 'Values';
		if (method_exists(__CLASS__, $method)) {
			$values = self::$method(true);
		}
		return $values;
	}

	public function l($string, $lang_id=null)
	{
		global $cookie;
		$id_lang = (!empty($lang_id)) ? intval($lang_id) : ((!isset($cookie) OR !is_object($cookie)) ? intval(Configuration::get('PS_LANG_DEFAULT')) : intval($cookie->id_lang));

		$file = _PS_MODULE_DIR_.$this->_module.'/'.Language::getIsoById($id_lang).'.php';
		// non compatible v1.2.5
		//if (Tools::file_exists_cache($file) AND include($file)) {
		if (file_exists($file) AND include($file)) {
			$_MODULES = $_MODULE;
		}

		if (!is_array($_MODULES))
			return (str_replace('"', '&quot;', $string));

		$string2 = str_replace('\'', '\\\'', $string);
		$currentKey = '<{'.$this->_module.'}prestashop>'.get_class($this).'_'.md5($string2);

		if (key_exists($currentKey, $_MODULES))
			$ret = stripslashes($_MODULES[$currentKey]);
		else
			$ret = $string;

		return str_replace('"', '&quot;', $ret);
	}

	// affiche la case permettant d'acheter le service SMS dans la page des transporteurs
	static public function displayCustomerChoice($params) {
		global $smarty, $cart;

		if (intval(Configuration::get('SENDSMS_FREEOPTION')) === 0) {
			$product = new Product(intval(Configuration::get('SENDSMS_ID_PRODUCT')));
			$price = $product->getPrice(true, NULL, 2);

			// faut-il cocher la case sur le FO ?
			$putInCart = 0;
			$result = $cart->containsProduct(intval(Configuration::get('SENDSMS_ID_PRODUCT')), 0, null);
			if (!empty($result['quantity']) || intval(Configuration::get('SENDSMS_PUT_IN_CART')) == 1) {
				$putInCart = 1;
			}

			$smarty->assign(array(
				'sendsmsPutInCart' => $putInCart,
				'sendsmsPrice' => $price
			));
			return true;
		}
		return false;
	}

	// enregistre la case permettant d'acheter le service SMS dans la page des transporteurs
	static public function processCustomerChoice($params) {
		global $cart;

		$customerChoice = $params['customerChoice'];

		// on retire l'éventuel notification SMS du panier
		$cart->deleteProduct(intval(Configuration::get('SENDSMS_ID_PRODUCT')));

		if ($customerChoice == 1 && intval(Configuration::get('SENDSMS_FREEOPTION')) === 0) {
			// on vérifie que le téléphone est bien renseigné
			$address = new Address($cart->id_address_invoice);
			if (empty($address->phone_mobile)) {
				return false;
			}

			$cart->updateQty(1, intval(Configuration::get('SENDSMS_ID_PRODUCT')));
		}
		return true;
	}

	// se déclenche lors de l'ajout d'un produit au panier, et vérifie que le service notification peut être acheté, et remet la quantité à 1
	static public function checkCartForSms($params) {
		global $cart;

		if (!$cart->isVirtualCart() && intval(Configuration::get('SENDSMS_FREEOPTION')) === 0) {
			$result = $cart->containsProduct(intval(Configuration::get('SENDSMS_ID_PRODUCT')), 0, null);
			if (!empty($result['quantity'])) {
				$cart->deleteProduct(intval(Configuration::get('SENDSMS_ID_PRODUCT')));
				$cart->updateQty(1, intval(Configuration::get('SENDSMS_ID_PRODUCT')));
			}
		} else {
			$cart->deleteProduct(intval(Configuration::get('SENDSMS_ID_PRODUCT')));
		}
	}

	private function _setPhone($addressId, $bAdmin)
	{
		self::$_phone = '';
		if ($bAdmin)
			self::$_phone = Configuration::get('SENDSMS_ADMIN_PHONE_NUMBER');
		else if (!empty($addressId)) {
			$address = new Address($addressId);
			if (!empty($address->phone_mobile) && !empty($address->id_country)) {
				self::$_phone = self::_convertPhoneToInternational($address->phone_mobile, $address->id_country);
			}
		}
	}

	private function _setRecipient($customer)
	{
		self::$_recipient = $customer;
	}

	private function _convertPhoneToInternational($phone, $id_country) {
		$phone = preg_replace("/[^+0-9]/", "", $phone);
		// compatibilité 1.2.5
		$country = new Country(intval($id_country));
		$iso = $country->iso_code;

		$result = Db::getInstance()->getRow("SELECT prefix FROM `" . _DB_PREFIX_ . "sendsms_phone_prefix` WHERE `iso_code` = '" . $iso . "'");
		$prefix = $result['prefix'];
		if (empty($prefix))
			return null;
		else {
			// s'il commence par + il est déjà international
			if (substr($phone, 0, 1) == '+') {
				return $phone;
			}
			// s'il commence par 00 on les enlève et on vérifie le code pays pour ajouter le +
			else if (substr($phone, 0, 2) == '00') {
				$phone = substr($phone, 2);
				if (strpos($phone, $prefix) === 0) {
					return '+' . $phone;
				} else {
					return null;
				}
			}
			// s'il commence par 0, on enlève le 0 et on ajoute le prefix du pays
			else if (substr($phone, 0, 1) == '0') {
				return '+' . $prefix . substr($phone, 1);
			}
			// s'il commence par le prefix du pays, on ajoute le +
			else if (strpos($phone, $prefix) === 0) {
				return '+' . $phone;
			}
			else {
				return '+' . $prefix . $phone;
			}
		}
	}

	private function _getBaseValues() {
		// non compatible v1.2.5
		// $host = 'http://'.Tools::getHttpHost(false, true);
		$host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
		$host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
		$host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$host;

		$values = array(
			'{shopname}' => Configuration::get('PS_SHOP_NAME'),
			'{shopurl}' => $host.__PS_BASE_URI__
		);
		return $values;
	}

	// Méthodes pour chacun des hooks gérés
	private function _getNewOrderValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
			$values = array(
				'{firstname}' => 'John',
				'{lastname}' => 'Doe',
				'{order_id}' => '000001',
				'{payment}' => 'Paypal',
				'{total_paid}' => '100',
				'{currency}' => $currency->sign
			);
		} else {
			$order = self::$_params['order'];
			$customer = self::$_params['customer'];
			$currency = self::$_params['currency'];

			// Si l'option est payante et que le client ne l'a pas mise dans son panier, on n'envoit rien.
			if (!$bAdmin && intval(Configuration::get('SENDSMS_FREEOPTION')) === 0) {
				$cart = self::$_params['cart'];
				$result = $cart->containsProduct(intval(Configuration::get('SENDSMS_ID_PRODUCT')), 0, null);
				if (!empty($result['quantity'])) {
					self::$_paid_by_customer = 1;
				} else {
					return false;
				}
			}

			if (!$bAdmin)
				self::_setRecipient($customer);
			self::_setPhone($order->id_address_invoice, $bAdmin);

			$values = array(
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{order_id}' => sprintf("%06d", $order->id),
				'{payment}' => $order->payment,
				'{total_paid}' => $order->total_paid,
				'{currency}' => $currency->sign
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getOrderReturnValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$values = array(
				'{return_id}' => '4',
				'{customer_id}' => '136',
				'{order_id}' => '1027',
				'{message}' => 'This is a message'
			);
		} else {
			self::_setPhone(null, true);

			$orderReturn = self::$_params['orderReturn'];
			$values = array(
				'{return_id}' => intval($orderReturn->id),
				'{customer_id}' => intval($orderReturn->id_customer),
				'{order_id}' => sprintf("%06d", $orderReturn->id_order),
				'{message}' => strval($orderReturn->question)
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getCreateAccountValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$values = array(
				'{firstname}' => 'John',
				'{lastname}' => 'Doe'
			);
		} else {
			$customer = self::$_params['newCustomer'];
			if (!$bAdmin)
				self::_setRecipient($customer);
			self::_setPhone(Address::getFirstCustomerAddressId($customer->id), $bAdmin);

			$values = array(
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getUpdateQuantityValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$values = array(
				'{product_id}' => '000001',
				'{product_ref}' => 'REF-001',
				'{product_name}' => 'Ipod Nano',
				'{quantity}' => '2'
			);
		} else {
			self::_setPhone(null, true);

			$product = self::$_params['product'];
			$quantity = intval(self::$_params['product']['quantity_attribute'] ? self::$_params['product']['quantity_attribute'] : self::$_params['product']['stock_quantity']) - intval(self::$_params['product']['cart_quantity']);
			if ($quantity <= intval(Configuration::get('PS_LAST_QTIES')))
			{
				$values = array(
					'{product_id}' => intval(self::$_params['product']['id_product']),
					'{product_ref}' => strval(self::$_params['product']['reference']),
					'{product_name}' => strval(self::$_params['product']['name']),
					'{quantity}' => $quantity
				);
			} else
				return false;
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getSendsmsContactFormValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$values = array(
				'{contact_name}' => 'webmaster',
				'{contact_mail}' => 'webmaster@prestashop.com',
				'{from}' => 'johndoe@gmail.com',
				'{message}' => 'This is a message'
			);
		} else {
			$contact = self::$_params['contact'];
			$customer = self::$_params['customer'];

			if (!$bAdmin && Validate::isLoadedObject($customer)) {
				self::_setRecipient($customer);
			}
			self::_setPhone(Address::getFirstCustomerAddressId($customer->id), $bAdmin);

			$values = array(
				'{contact_name}' => strval($contact->name),
				'{contact_mail}' => strval($contact->email),
				'{from}' => strval(self::$_params['from']),
				'{message}' => strval(html_entity_decode(self::$_params['message'], ENT_QUOTES, 'UTF-8'))
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getSendsmsAdminAlertValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$values = array(
				'{balance}' => number_format('10', 3, ',', ' '),
			);
		} else {
			// si l'alerte est active (> 0) et que le message n'a pas déjà été envoyé
			// et que le nb de SMS restant est < à la limite donnée, alors on envoit
			$alertLevel = intval(Configuration::get('SENDSMS_ALERT_LEVEL'));
			if (intval(Configuration::get('SENDSMS_ALERT_SENT')) == 0 && $alertLevel > 0 && self::isAccountAvailable(Configuration::get('SENDSMS_EMAIL'), Configuration::get('SENDSMS_PASSWORD'), Configuration::get('SENDSMS_KEY')) && floatval(self::$_balance) <= floatval($alertLevel)) {
				Configuration::updateValue('SENDSMS_ALERT_SENT', '1');
				// compatibilité 1.2.5
				Configuration::set('SENDSMS_ALERT_SENT', '1');
				self::_setPhone(null, true);
				$values = array(
					'{balance}' => number_format(self::$_balance, 3, ',', ' ')
				);
			} else
				return null;
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getSendsmsCustomerAlertValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$values = array(
				'{firstname}' => 'John',
				'{lastname}' => 'Doe',
				'{product}' => 'Ipod Nano',
			);
		} else {
			$customer = self::$_params['customer'];
			$product = self::$_params['product'];
			self::_setRecipient($customer);
			self::_setPhone(Address::getFirstCustomerAddressId($customer->id), false);
			$values = array(
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{product}' => (is_array($product->name) ? $product->name[intval(Configuration::get('PS_LANG_DEFAULT'))] : $product->name)
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getSendsmsLostPasswordValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$values = array(
				'{firstname}' => 'John',
				'{lastname}' => 'Doe',
				'{password}' => 'YourNewPass',
			);
		} else {
			$customer = self::$_params['customer'];
			$password = self::$_params['password'];
			self::_setRecipient($customer);
			self::_setPhone(Address::getFirstCustomerAddressId($customer->id), false);
			$values = array(
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{password}' => $password
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getSendsmsDailyReportValues($bSimu = false, $bAdmin = false)
	{
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		if ($bSimu) {
			$values = array(
				'{date}' => date('Y-m-d'),
				'{subs}' => '5',
				'{visitors}' => '42',
				'{visits}' => '70',
				'{orders}' => '8',
				'{day_sales}' => Tools::displayPrice(50, $currency, false),
				'{month_sales}' => Tools::displayPrice(1000, $currency, false),
			);
		} else {
			// si le message n'a pas déjà été envoyé
			self::_setPhone(null, true);
			$values = array(
				'{date}' => date('Y-m-d'),
				'{subs}' => self::$_params['subs'],
				'{visitors}' => self::$_params['visitors'],
				'{visits}' => self::$_params['visits'],
				'{orders}' => self::$_params['orders'],
				'{day_sales}' => self::$_params['day_sales'],
				'{month_sales}' => self::$_params['month_sales']
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getPostUpdateOrderStatusValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$values = array(
				'{firstname}' => 'John',
				'{lastname}' => 'Doe',
				'{order_id}' => '000001',
				'{order_state}' => 'xxx'
			);
		} else {
			$order = new Order(intval(self::$_params['id_order']));
			$state = self::$_params['newOrderStatus']->name[$order->id_lang];
			$customer = new Customer(intval($order->id_customer));

			// Si l'option est payante et que le client ne l'a pas mise dans son panier, on n'envoit rien.
			if (intval(Configuration::get('SENDSMS_FREEOPTION')) === 0) {
				$cart = Cart::getCartByOrderId(self::$_params['id_order']);
				$result = $cart->containsProduct(intval(Configuration::get('SENDSMS_ID_PRODUCT')), 0, null);
				if (!empty($result['quantity'])) {
					self::$_paid_by_customer = 1;
				} else {
					return false;
				}
			}

			self::_setRecipient($customer);
			self::_setPhone(Address::getFirstCustomerAddressId($customer->id), false);

			$values = array(
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{order_id}' => sprintf("%06d", $order->id),
				'{order_state}' => $state
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	private function _getSendsmsShippingNumberValues($bSimu = false, $bAdmin = false)
	{
		if ($bSimu) {
			$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
			$values = array(
				'{firstname}' => 'John',
				'{lastname}' => 'Doe',
				'{order_id}' => '000001',
				'{shipping_number}' => 'ABC001',
				'{payment}' => 'Paypal',
				'{total_paid}' => '100',
				'{currency}' => $currency->sign
			);
		} else {
			$order = self::$_params['order'];
			$customer = self::$_params['customer'];
			$carrier = self::$_params['carrier'];
			$currency = new Currency($order->id_currency);

			// Si l'option est payante et que le client ne l'a pas mise dans son panier, on n'envoit rien.
			if (intval(Configuration::get('SENDSMS_FREEOPTION')) === 0) {
				$cart = Cart::getCartByOrderId($order->id);
				$result = $cart->containsProduct(intval(Configuration::get('SENDSMS_ID_PRODUCT')), 0, null);
				if (!empty($result['quantity'])) {
					self::$_paid_by_customer = 1;
				} else {
					return false;
				}
			}

			self::_setRecipient($customer);
			self::_setPhone(Address::getFirstCustomerAddressId($customer->id), false);

			$values = array(
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{order_id}' => sprintf("%06d", $order->id),
				'{shipping_number}' => $order->shipping_number,
				'{payment}' => $order->payment,
				'{total_paid}' => $order->total_paid,
				'{currency}' => $currency->sign
			);
		}
		return array_merge($values, self::_getBaseValues());
	}

	static public function replaceForGSM7($txt) {
  		$search  = array('À','Á','Â','Ã','È','Ê','Ë','Ì','Í','Î','Ï','Ð','Ò','Ó','Ô','Õ','Ù','Ú','Û','Ý','Ÿ','á','â','ã','ê','ë','í','î','ï','ð','ó','ô','õ','ú','û','µ','ý','ÿ','ç','Þ','°', '¨', '^', '«', '»', '|', '\\');
		$replace = array('A','A','A','A','E','E','E','I','I','I','I','D','O','O','O','O','U','U','U','Y','Y','a','a','a','e','e','i','i','i','o','o','o','o','u','u','u','y','y','c','y','o', '-', '-', '"', '"', 'I', '/');
		return str_replace($search, $replace, $txt);
	}

	static public function isGSM7($txt) {
		if (preg_match("/^[ÀÁÂÃÈÊËÌÍÎÏÐÒÓÔÕÙÚÛÝŸáâãêëíîïðóôõúûµýÿçÞ°{|}~¡£¤¥§¿ÄÅÆÇÉÑÖØÜßàäåæèéìñòöøùü,\.\-!\"#$%&()*+\/:;<=>?@€\[\]\^\w\s\\']*$/u", $txt))
			return true;
		else
			return false;
	}

	static public function notGSM7($txt) {
		return preg_replace("/[ÀÁÂÃÈÊËÌÍÎÏÐÒÓÔÕÙÚÛÝŸáâãêëíîïðóôõúûµýÿçÞ°{|}~¡£¤¥§¿ÄÅÆÇÉÑÖØÜßàäåæèéìñòöøùü,\.\-!\"#$%&()*+\/:;<=>?@€\[\]\^\w\s\\']/u", "", $txt);
	}
}
?>