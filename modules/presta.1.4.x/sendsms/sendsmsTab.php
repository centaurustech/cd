<?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

include_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsGenericTab.php');

class sendsmsTab extends sendsmsGenericTab
{
	private $_nbStandart = 0;
	private $_nbSms = 0;

	public function postProcess() {
		if (Tools::isSubmit('submitAccount') || Tools::isSubmit('submitSettings')) {
			$this->_postValidation();

			if (!sizeof($this->_errors))
			{
				if (Tools::isSubmit('submitAccount')) {
					if (!Tools::getValue('email') || !Tools::getValue('password') || !Tools::getValue('key') || !sendsmsManager::isAccountAvailable(Tools::getValue('email'), Tools::getValue('password'), Tools::getValue('key'))) {
						$this->_errors[] = Tools::displayError($this->l('Update failed : This account is not a valid account on www.smsworldsender.com'));
					} else {
						Configuration::updateValue('SENDSMS_EMAIL', Tools::getValue('email'));
						Configuration::updateValue('SENDSMS_PASSWORD', Tools::getValue('password'));
						Configuration::updateValue('SENDSMS_KEY', Tools::getValue('key'));
						// compatibilité 1.2.5
						Configuration::set('SENDSMS_EMAIL', Tools::getValue('email'));
						Configuration::set('SENDSMS_PASSWORD', Tools::getValue('password'));
						Configuration::set('SENDSMS_KEY', Tools::getValue('key'));
						$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif"/> '.$this->l('Settings updated').'</div>';
					}
				} else if (Tools::isSubmit('submitSettings')) {
					Configuration::updateValue('SENDSMS_ADMIN_PHONE_NUMBER', Tools::getValue('admin_phone'));
					Configuration::updateValue('SENDSMS_SENDER', Tools::getValue('sender'));
					Configuration::updateValue('SENDSMS_FREEOPTION', Tools::getValue('freeoption'));
					Configuration::updateValue('SENDSMS_SIMULATION', Tools::getValue('simulation'));
					Configuration::updateValue('SENDSMS_PUT_IN_CART', Tools::getValue('putincart'));
					Configuration::updateValue('SENDSMS_ID_PRODUCT', Tools::getValue('id_product'));
					Configuration::updateValue('SENDSMS_ALERT_LEVEL', Tools::getValue('admin_alert'));
					$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif"/> '.$this->l('Settings updated').'</div>';
				}
			}
		}
	}

	public function display()
	{
		$this->_displayHeader();
		$this->_displayForm();
		$this->_displayInfo();
		echo $this->_html;
	}

	private function _displayHeader()
	{
		$this->_html .= '
		<style>
			div.margin-form {
			 	clear: both;
			 	padding: 0 0 1em 100px;
			}

			label {
				width: auto;
				text-align: left;
				padding-top: 10px;
				padding-bottom: 5px;
			}
		</style>
		<h2>'.$this->l('SendSMS Module - General settings').'</h2>
		<div style="float: right; width: 295px; height: 180px; border: dashed 1px #268CCD; padding: 8px; margin-left: 12px; margin-top: -30px;">
			<h2 style="text-align: center">'.$this->l('Opening your SMSWorldSender account').'</h2>
			<div style="clear: both;"></div>
			<p style="text-align: justify">'.$this->l('Create your account by clicking on the following image, and start sending SMS now to improve your sells !').'</p>
			<p style="text-align: center;"><a href="http://www.smsworldsender.com/envoyer-sms-inscription.php" target="_blank"><img src="../modules/'.$this->_module.'/smsworldsender.png" style="margin-top: 15px" alt="SMSWorldSender"/></a></p>
			<div style="clear: right;"></div>
		</div>
		<div style="text-align: justify">
			<b>'.$this->l('This module allows you to send SMS to the admin, or to customers on differents event.').'</b><br /><br />
			'.$this->l('First, you have to create an account on') . ' <b><a href="http://www.smsworldsender.com/envoyer-sms-inscription.php" target="_blank">www.smsworldsender.com</a></b> ' . $this->l('to be able to send SMS, and make a deposit on this account.').'<br /><br />'.
			$this->l('Then, please fill your identification settings, and set your options.').'<br/>'.
			$this->l('If you want that customers pay for the notification service, first create a product representing the SMS service, then fill the product ID field.').'<br/><br/>'.
			$this->l('Finaly, you have to activate/desactivate events you want on the "Messages" tab, and if needed, customize the text that will be sent for each event.').'<br/><br/>'.
			$this->l('Enjoy !').'
		</div>
		<div style="clear:both;">&nbsp;</div>';
	}

 private function _displayForm()
	{
		$simulation = Tools::getValue('simulation', Configuration::get('SENDSMS_SIMULATION'));
		$freeOption = Tools::getValue('freeoption', Configuration::get('SENDSMS_FREEOPTION'));
		$putInCart = Tools::getValue('putincart', Configuration::get('SENDSMS_PUT_IN_CART'));

		$bAuth = false;
		$xml = false;
		if (Configuration::get('SENDSMS_EMAIL') != '' && Configuration::get('SENDSMS_PASSWORD') != '' && Configuration::get('SENDSMS_KEY') != '') {
			$bAuth = true;
			sendsmsManager::isAccountAvailable(Configuration::get('SENDSMS_EMAIL'), Configuration::get('SENDSMS_PASSWORD'), Configuration::get('SENDSMS_KEY'));
			$xml = sendsmsManager::getXmlRss(Configuration::get('SENDSMS_EMAIL'), Configuration::get('SENDSMS_PASSWORD'), Configuration::get('SENDSMS_KEY'));
		}

		$this->_html .= '
		<script>
			$(function(){' .
				((!isset($freeOption) || $freeOption == '1') ? '$(\'#paying_options\').hide();' : '$(\'#paying_options\').show();') .
			'});
		</script>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<div style="float: right; width: 30%">
				<fieldset style="height: 295px">
	      		<legend><img src="../img/t/AdminLocalization.gif" /> '.$this->l('News from smsworldsender.com').'</legend>
	      		<div style="overflow: auto; height: 100%; font-size: 10px; text-align: justify">' .
	      			$this->_readXml($xml) . '
	      		</div>
				</fieldset>
			</div>
			<div style="width: 68%">
				<fieldset style="height: 295px">
	      		<legend><img src="../img/admin/htaccess.gif" /> '.$this->l('Account settings on smsworldsender.com').'</legend>' .
	      		(($bAuth) ? '<label style="font-weight: normal"><img src="../img/admin/enabled.gif"/> ' . $this->l('Congratulation, you\'re connected to www.smsworldsender.com !') . '</label><br/><br/>' : '<label style="font-weight: normal"><img src="../img/admin/disabled.gif"/> ' . $this->l('Please, enter your account information to connect to www.smsworldsender.com') . '</label><br/><br/>') .
				(($bAuth) ? '<div class="clear center"><b>' . $this->l('Amout on your account') . ' : ' . number_format(sendsmsManager::getBalance(), 3, ',', ' ') . ' ' . $this->l('euro') . '</b><br/></div>' : '') . '
					<label>'.$this->l('Email').'</label>
					<div class="margin-form">
						<input type="text" size="40" name="email" value="' . Tools::getValue('email', Configuration::get('SENDSMS_EMAIL')) . '"/>
					</div>
					<label>'.$this->l('Password').'</label>
					<div class="margin-form">
						<input type="password" size="40" name="password" />
					</div>
					<label>'.$this->l('Key prestashop').'</label>
					<div class="margin-form">
						<input type="text" size="40" name="key" maxlength="255" value="' . Tools::getValue('key', Configuration::get('SENDSMS_KEY')) . '"/><br/>' . $this->l('You can get it in your account information, on website') . '
					</div>
					<div class="clear center">
						<input type="submit" name="submitAccount" value="'.$this->l('Update').'" class="button" />
					</div>
				</fieldset>
			</div>
		</form>
		<br/><br/>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<div style="float: right; width: 30%">
				<fieldset style="height: 295px">
	      		<legend><img src="../img/admin/prefs.gif" /> '.$this->l('Files\' configuration').'</legend>
	      		<div style="overflow: auto; height: 100%; font-size: 10px; text-align: justify">' .
	      			$this->_checkFiles() . '
	      		</div>
				</fieldset>
			</div>
			<div style="width: 68%">
				<fieldset>
		      		<legend><img src="../img/admin/prefs.gif" /> '.$this->l('SendSMS Settings').'</legend>
		      		<label>'.$this->l('Admin mobile number').'</label>
					<div class="margin-form">
						<input type="text" size="15" maxlength="15" name="admin_phone" value="' . Tools::getValue('admin_phone', Configuration::get('SENDSMS_ADMIN_PHONE_NUMBER')) . '"/> ' . $this->l('ex : +33612345678') . '
					</div>
		      		<label>'.$this->l('Sender name').'</label>
					<div class="margin-form">
						<input type="text" size="16" maxlength="16" name="sender" value="' . Tools::getValue('sender', Configuration::get('SENDSMS_SENDER')) . '"/> ' . $this->l('11 chars max (letters + digits), or 16 digits max') . '
					</div>
					<label>'.$this->l('Simulation').'</label>
					<div class="margin-form">
						<input ' . (!isset($simulation) || $simulation == '0' ? 'checked' : '') . ' type="radio" id="simulation_0" name="simulation" value="0"/>
						<label class="t" for="simulation_0"> ' . $this->l('No') . '</label>
						<input style="margin-left: 10px" ' . ($simulation == '1' ? 'checked' : '') . ' type="radio" id="simulation_1" name="simulation" value="1"/>
						<label class="t" for="simulation_1"> ' . $this->l('Yes') . '</label>
					</div>
					<label>'.$this->l('Send alert when account under').'</label>
					<div class="margin-form">
						<input type="text" size="5" maxlength="5" name="admin_alert" value="' . Tools::getValue('admin_alert', Configuration::get('SENDSMS_ALERT_LEVEL')) . '"/> ' . $this->l('euro') . '
					</div>
					<label>'.$this->l('Free order\'s notification').'</label>
					<div class="margin-form">
						<input ' . (!isset($freeOption) || $freeOption == '1' ? 'checked' : '') . ' type="radio" id="freeoption_1" name="freeoption" value="1" onClick="$(\'#paying_options\').hide()"/>
						<label class="t" for="freeoption_1"> ' . $this->l('Yes') . '</label>
						<input style="margin-left: 10px" ' . ($freeOption === '0' ? 'checked' : '') . ' type="radio" id="freeoption_0" name="freeoption" value="0" onClick="$(\'#paying_options\').show()"/>
						<label class="t" for="freeoption_0"> ' . $this->l('No, customer have to pay the option') . '</label><br>'.
						$this->l('Warning : Paid option is not compatible with cart that contains only virtuals products') .'
					</div>
					<div id="paying_options" ' . (!isset($freeOption) || $freeOption == '1' ? 'style="display: none"' : '') . '>
						<label>'.$this->l('Automatically put SMS notification in cart ?').'</label>
						<div class="margin-form">
							<input ' . (!isset($putInCart) || $putInCart == '0' ? 'checked' : '') . ' type="radio" id="putincart_0" name="putincart" value="0"/>
							<label class="t" for="putincart_0"> ' . $this->l('No, customer will have to check a box') . '</label>
							<input style="margin-left: 10px" ' . ($putInCart == '1' ? 'checked' : '') . ' type="radio" id="putincart_1" name="putincart" value="1"/>
							<label class="t" for="putincart_1"> ' . $this->l('Yes, but customer may remove it') . '</label>
						</div>
						<label>'.$this->l('SMS notification product ID').'</label>
						<div class="margin-form">
							<input type="text" size="6" maxlength="6" name="id_product" value="' . Tools::getValue('id_product', Configuration::get('SENDSMS_ID_PRODUCT')) . '"/>
						</div>
					</div>
					<div class="clear center">
						<input type="submit" name="submitSettings" value="'.$this->l('Update').'" class="button" />
					</div>
				</fieldset>
			</div>
		</form>';
	}

	private function _displayInfo() {
		$this->_html .= '
		<br /><br />
		<fieldset style="width: 97%">
			<legend><img src="../img/admin/warning.gif" /> '.$this->l('Informations').'</legend>
			<p style="clear: both;">' .
				$this->l('Standart message is divided in 160 chars long parts, and each part cost 1 SMS, if all chars are in list below. If one or more chars are not in this list, then message is divided in 70 chars parts, and each part cost 1 SMS. You can see if your message will be divided in 70 chars long parts in the "Manage message" tab, and which char is not supported in standart.') . '
			</p>
			<p>
				<br><b>' . $this->l('Before applying mentioned rule, these characters are automatically replaced') . '</b>
				<br/><div style="float: left; width: 130px">' . $this->l('Original character') . '</div>: À Á Â Ã È Ê Ë Ì Í Î Ï Ð Ò Ó Ô Õ Ù Ú Û Ý Ÿ á â ã ê ë í î ï ð ó ô õ ú û µ ý ÿ ç Þ ° ¨ ^ « » | \
				<br style="clear: both"/><div style="float: left; width: 130px">' . $this->l('Replaced by') . '</div>: A A A A E E E I I I I D O O O O U U U Y Y a a a e e i i i o o o o u u u y y c y o - - " " I /
			</p>
			<p style="clear: both">
				<br><b>' . $this->l('Authorized characters') . '</b>
				<br/>0 1 2 3 4 5 6 7 8 9
				<br/>a A b B c C d D e E f F g G h H i I j J k K l L m M n N o O p P q Q r R s S t T u U v V w W x X y Y z Z
				<br/>à À á Á â Â ã Ã ä Ä å Å æ Æ ç Ç è È ê Ê ë Ë é É ì Ì í Í î Î ï Ï ð Ð ñ Ñ ò Ò ó Ó ô Ô õ Õ ö Ö ø Ø ù Ù ú Ú û Û ü Ü ÿ Ÿ ý Ý Þ ß
				<br/>{ } ~ ¤ ¡ ¿ ! ? " # $ % & \' ^ * + - _ , . / : ; < = > § @ ( ) [ ]
				<!--<br/>Γ Δ Θ Λ Ξ Π Σ Φ Ψ Ω € £ ¥-->
				<br/><br/>' . $this->l('These chars count as 2 chars :') . ' { } € [ ] ~
			</p>
		</fieldset>';
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('submitAccount')) {
			if (!Tools::getValue('email') || !Tools::getValue('password') || !Tools::getValue('key')) {
				$this->_errors[] = Tools::displayError($this->l('Please enter your account information to login to www.smsworldsender.com'));
			} else if (Tools::getValue('email') && !Validate::isEmail(Tools::getValue('email')))
				$this->_errors[] = Tools::displayError($this->l('The email you entered is not a valid email'));
		} else if (Tools::isSubmit('submitSettings')) {
			if (!Tools::getValue('sender'))
				$this->_errors[] = Tools::displayError($this->l('You have to set a sender name'));
			if(!preg_match('/^[[:digit:]]{1,16}$/', Tools::getValue('sender')) && !preg_match('/^[[:alnum:]]{1,11}$/', Tools::getValue('sender'))) {
				$this->_errors[] = Tools::displayError($this->l('Please enter a valid sender name : 11 chars max (letters + digits), or 16 digits max'));
			}
			if (!Tools::getValue('admin_phone') || !Validate::isPhoneNumber(Tools::getValue('admin_phone')) || !preg_match('/^\+[0-9]{6,16}$/', Tools::getValue('admin_phone')))
				$this->_errors[] = Tools::displayError($this->l('Please enter a valid admin mobile number'));
			if (Tools::getValue('admin_alert') && !Validate::isInt(Tools::getValue('admin_alert')))
				$this->_errors[] = Tools::displayError($this->l('Please enter a valid integer value for alert'));
			$id_product = Tools::getValue('id_product');
			if (Tools::getValue('freeoption') === '0') {
				if (empty($id_product))
					$this->_errors[] = Tools::displayError($this->l('Please enter the SMS notification\'s product id'));
				else {
					$product = new Product($id_product);
					if ($product->id) {
						if (intval($product->quantity) <= 0)
							$this->_errors[] = Tools::displayError($this->l('Product specified is out of stock'));
						$product->active = 1;
						$product->date_add = '1970-01-01 00:00:00';
						$product->update();
					} else {
						$this->_errors[] = Tools::displayError($this->l('Please enter a valid product ID for SMS notification'));
					}
				}
			} else if (!empty($id_product)) {
				$product = new Product($id_product);
				if ($product->id) {
					$product->active = 0;
					$product->update();
				}
			}
		}
	}

	private function _readXml($xml) {
		global $cookie;

		$result = '';
		if ($xml) {
			$doc = new DOMDocument('1.0', 'UTF-8');
			@$doc->loadXML($xml);
			$newslist = $doc->getElementsByTagName('news');

			$result = '';
			foreach($newslist as $news) {
				if (!empty($result))
					$result .= '<br><br>';

				$suffix = (Language::getIsoById($cookie->id_lang) == 'fr') ? '_fr' : '_en';
				$date = $news->getElementsByTagName('date')->item(0)->nodeValue;
				$localDate = (Language::getIsoById($cookie->id_lang) == 'fr') ? date('d/m/Y', strtotime($date)) : date('Y-m-d', strtotime($date));
				$title = $news->getElementsByTagName('title'.$suffix)->item(0)->nodeValue;
				$text = $news->getElementsByTagName('text'.$suffix)->item(0)->nodeValue;
				if ((strtotime(date('Y-m-d 23:59:59')) - strtotime($date)) / 3600 / 24 > 5) {
					$result .= '<b>' . $title . '</b><br>';
				} else {
					$result .= '<img src="../img/admin/news-new.gif"> <b>' . $title . '</b><br>';
				}
				$result .= '<i>' . $localDate . '</i><br>' . nl2br($text);
			}
		}
		return $result;
	}

	// check if files are correctly configurated
	private function _checkFiles() {
		$result = $this->l('This block shows which files used by sendsms seems to be configurated or not') . "<br><br>";

		$files = array(
			'./tabs/AdminOrders.php',
			'../themes/' . _THEME_NAME_ . '/order-carrier.tpl',
			'../modules/mailalerts/mailalerts.php',
			'../override/controllers/CartController.php',
			'../override/controllers/OrderController.php',
			'../override/controllers/ContactController.php',
			'../override/controllers/PasswordController.php'
		);
		foreach($files as $filename) {
			if (file_exists($filename)) {
				$fd = fopen($filename, 'r');
				$contents = fread($fd, filesize($filename));
				if (stripos($contents, "sendsms") !== false) {
					$result .= "<img src='/img/admin/ok.gif' align='absmiddle'> " . basename($filename) ."<br/>";
				} else {
					$result .= "<img src='/img/admin/error2.png' align='absmiddle'> " . basename($filename) . "<br>" . $this->l('This file doesn\'t contain the sendsms modification') . "<br/>";
				}
				fclose($fd);
			} else {
				$result .= "<img src='/img/admin/error2.png' align='absmiddle'> " . basename($filename) . "<br>" . $this->l('Must be copied into /override/controllers/') . "<br/>";
			}
		}
		return $result;
	}
}
?>