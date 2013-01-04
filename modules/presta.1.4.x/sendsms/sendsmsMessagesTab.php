<?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

include_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsGenericTab.php');

class sendsmsMessagesTab extends sendsmsGenericTab
{
	private $_sendsmsManager;
	private $_orderStates;
	private $_divLangName = '';

	public function __construct()
	{
		global $cookie;

		parent::__construct();

		$this->_sendsmsManager = new sendsmsManager();
		$this->_orderStates = OrderState::getOrderStates(intval($cookie->id_lang));
	}

	public function postProcess() {
		if (Tools::isSubmit('submit')) {
			$this->_postValidation();

			if (!sizeof($this->_errors))
			{
				$config = $this->_sendsmsManager->getConfig();
				if (is_array($config)) {
					foreach($config as $hook_id => $values) {
						// SMS pour l'admin
						if ($values[1] == sendsmsManager::__SENDSMS_ADMIN__ || $values[1] == sendsmsManager::__SENDSMS_BOTH__) {
							$keyActive = 'SENDSMS_ISACTIVE_' . $hook_id . '_ADMIN';
							$keyTxt = 'SENDSMS_TXT_' . $hook_id . '_ADMIN';

							if(!_PS_MAGIC_QUOTES_GPC_)
								$_POST[$keyTxt] = addslashes($_POST[$keyTxt]);

							Configuration::updateValue($keyActive, Tools::getValue($keyActive));
							Configuration::updateValue($keyTxt, sendsmsManager::replaceForGSM7(trim(Tools::getValue($keyTxt))));
							// compatibilité 1.2.5
							Configuration::set($keyActive, Tools::getValue($keyActive));
							Configuration::set($keyTxt, sendsmsManager::replaceForGSM7(trim(Tools::getValue($keyTxt))));
						}
						// SMS pour le client
						if ($values[1] == sendsmsManager::__SENDSMS_CUSTOMER__ || $values[1] == sendsmsManager::__SENDSMS_BOTH__) {
							// cas particulier du hook postUpdateOrderStatus, on boucle sur tous les status possibles
							if ($values[0] == 'postUpdateOrderStatus') {
								foreach ($this->_orderStates AS $state) {
									$keyActive = 'SENDSMS_ISACTIVE_' . $hook_id . '_' . $state['id_order_state'];
									Configuration::updateValue($keyActive, Tools::getValue($keyActive));

									$texts = array();
									$keyTxt = 'SENDSMS_TXT_' . $hook_id . '_' . $state['id_order_state'];
									foreach (Language::getLanguages() as $language) {
										if(!_PS_MAGIC_QUOTES_GPC_)
											$_POST[$keyTxt . '_' . $language['id_lang']] = addslashes($_POST[$keyTxt . '_' . $language['id_lang']]);
										$texts[$language['id_lang']] = sendsmsManager::replaceForGSM7(trim(Tools::getValue($keyTxt . '_' . $language['id_lang'])));
									}
									Configuration::updateValue($keyTxt, $texts);
								}
							} else {
								$keyActive = 'SENDSMS_ISACTIVE_' . $hook_id;
								Configuration::updateValue($keyActive, Tools::getValue($keyActive));

								$texts = array();
								$keyTxt = 'SENDSMS_TXT_' . $hook_id;
								foreach (Language::getLanguages() as $language) {
									if(!_PS_MAGIC_QUOTES_GPC_)
										$_POST[$keyTxt . '_' . $language['id_lang']] = addslashes($_POST[$keyTxt . '_' . $language['id_lang']]);
									$texts[$language['id_lang']] = sendsmsManager::replaceForGSM7(trim(Tools::getValue($keyTxt . '_' . $language['id_lang'])));
								}
								Configuration::updateValue($keyTxt, $texts);
							}
						}
					}
				}
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif"/> '.$this->l('Settings updated').'</div>';
			}
		}
		else if (Tools::isSubmit('reset')) {
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` like \'SENDSMS_TXT_%\'');
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif"/> '.$this->l('Settings updated').'</div>';
			Configuration::loadConfiguration();
		}
	}

	public function display()
	{
		$this->_displayHeader();
		$this->_displayForm();
		echo $this->_html;
 	}

 	private function _displayHeader()
	{
		$this->_html .= '
		<style>
			div.margin-form {
			 	clear: both;
			 	padding: 0 0 1em 110px;
			}

			label {
				width: auto;
				text-align: left;
				padding-bottom: 5px;
			}
		</style>
		<h2>'.$this->l('SendSMS Module - Manage messages').'</h2>
		<b>'.$this->l('Choose SMS you want to activate, and customize their text.').'</b><br /><br />'.
		$this->l('On the right side you can see a preview, to check that everything is ok.').'<br /><br />
		<div style="clear:both;">&nbsp;</div>';
	}

 	private function _displayForm()
	{
		global $cookie;

		$defaultLanguage = intval($cookie->id_lang);
		$adminHtml = '';
		$customerHtml = '';
		$config = $this->_sendsmsManager->getConfig();

		if (is_array($config)) {
			// On boucle sur les hook destinés au client pour créer la chaine divLangName servant a l'affichage du flag
			foreach($config as $hook_id => $values) {
				if ($values[1] == sendsmsManager::__SENDSMS_CUSTOMER__ || $values[1] == sendsmsManager::__SENDSMS_BOTH__) {
					// cas particulier du hook postUpdateOrderStatus, on boucle sur tous les status possibles
					if ($values[0] == 'postUpdateOrderStatus') {
						foreach ($this->_orderStates AS $state) {
							$this->_divLangName .= (empty($this->_divLangName) ? '' : '¤') . 'SENDSMS_TXT_' . $hook_id . '_' . $state['id_order_state'];
						}
					} else {
						$this->_divLangName .= (empty($this->_divLangName) ? '' : '¤') . 'SENDSMS_TXT_' . $hook_id;
					}
				}
			}
			// On boucle sur les hook pour générer leur code html
			foreach($config as $hook_id => $values) {
				if ($values[1] == sendsmsManager::__SENDSMS_ADMIN__ || $values[1] == sendsmsManager::__SENDSMS_BOTH__) {
					$adminHtml .= $this->_getCode($hook_id, $values[0], true);
				}
				if ($values[1] == sendsmsManager::__SENDSMS_CUSTOMER__ || $values[1] == sendsmsManager::__SENDSMS_BOTH__) {
					if ($values[0] == 'postUpdateOrderStatus') {
						foreach ($this->_orderStates AS $state) {
							$customerHtml .= $this->_getCode($hook_id . '_' . $state['id_order_state'], $values[0], false, $state['name'], true);
						}
					} else {
						if ($values[0] == 'newOrder')
							$customerHtml .= $this->_getCode($hook_id, $values[0], false, null, true);
						else
							$customerHtml .= $this->_getCode($hook_id, $values[0]);
					}
				}
			}
		}

		$this->_html .= '
		<script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
      			<legend><img src="../img/admin/tab-customers.gif">'.$this->l('SMS for Admin').'</legend>'.
				$adminHtml.'
				<div class="clear center">
					<input type="submit" name="submit" value="'.$this->l('Update').'" class="button" />
				</div>
			</fieldset>
			<br><br>
			<fieldset>
				<legend><img src="../img/admin/tab-groups.gif">'.$this->l('SMS for Customer').'</legend>'.
				$customerHtml.'
				<div class="clear center">
					<input type="submit" name="submit" value="'.$this->l('Update').'" class="button" />
				</div>
			</fieldset>
			<div class="clear center">
				<br><input type="submit" name="reset" value="'.$this->l('Reset all messages').'" class="button" />
			</div>
		</form>';
	}

	private function _getCode($hook_id, $hook_name, $bAdmin=false, $comment=null, $bPaid=false) {
		global $cookie;

		$defaultLanguage = intval($cookie->id_lang);
		$keyActive = 'SENDSMS_ISACTIVE_' . $hook_id . (($bAdmin) ? '_ADMIN' : '');

		$values = $this->_sendsmsManager->getSmsValuesForTest($hook_name);
		$code  = '<label>'. $this->_sendsmsManager->getLabel($hook_name);
		// cas particulier du hook postUpdateOrderStatus, on ajoute le libellé du statut
		if (!empty($comment)) {
			$code  .= ' <span style="font-weight: normal">('. $comment . ')</span>';
		}
		if ($bPaid && intval(Configuration::get('SENDSMS_FREEOPTION')) === 0) {
			$code  .= '<br><span style="font-weight: normal">'. $this->l('Sent only if customer pay the option') . '</span>';
		}
		$code .= '</label>';
		$code .= '<div class="margin-form">';
		$code .= '	<div style="padding-bottom: 4px"><input '.(Configuration::get($keyActive)==1 ? 'checked':'').' type="checkbox" name="'.$keyActive.'" value="1"/> '.$this->l('actif ?').'</div>';
		if ($bAdmin) {
			$key = 'SENDSMS_TXT_' . $hook_id . '_ADMIN';
			$txt = sendsmsManager::replaceForGSM7(Configuration::get($key) ? Configuration::get($key) : $this->_sendsmsManager->getLabel($hook_name . '_default_admin'));
			$txtTest = sendsmsManager::replaceForGSM7(str_replace(array_keys($values), array_values($values), $txt));
			$bGSM7 = sendsmsManager::isGSM7($txtTest);

			$code .= '<textarea style="overflow: auto" name="'.$key.'" rows="4" cols="50"/>'.$txt.'</textarea>
					  <textarea style="overflow: auto; background-color: transparent; color: #7F7F7F; margin-left: 45px" readonly rows="4" cols="50"/>'.$txtTest.'</textarea>' .
					  (!$bGSM7 ? '<div class="clear" style="padding-top: 4px"><img src="../img/admin/warning.gif"> ' . $this->l('This message will be divided in 70 chars parts, because of non standart characters : ') . sendsmsManager::notGSM7($txtTest) . '</div>' : '<div class="clear" style="padding-top: 4px">' . $this->l('This message will be divided in 160 chars parts') . '</div>');
		} else {
			foreach (Language::getLanguages() as $language) {
				$key = 'SENDSMS_TXT_' . $hook_id;
				$txt = sendsmsManager::replaceForGSM7(Configuration::get($key, $language['id_lang']) ? Configuration::get($key, $language['id_lang']) : $this->_sendsmsManager->getLabel($hook_name . '_default_customer', $language['id_lang']));
				$txtTest = sendsmsManager::replaceForGSM7(str_replace(array_keys($values), array_values($values), $txt));
				$bGSM7 = sendsmsManager::isGSM7($txtTest);

				$code .= '<div id="'.$key.'_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
							<textarea style="overflow: auto" name="'.$key.'_'.$language['id_lang'].'" rows="4" cols="50"/>'.$txt.'</textarea>
							<textarea style="overflow: auto; background-color: transparent; color: #7F7F7F; margin-left: 45px" readonly rows="4" cols="50"/>'.$txtTest.'</textarea>' .
							(!$bGSM7 ? '<div class="clear" style="padding-top: 4px"><img src="../img/admin/warning.gif"> ' . $this->l('This message will be divided in 70 chars parts, because of non standart characters : ') . sendsmsManager::notGSM7($txtTest) . '</div>' : '<div class="clear" style="padding-top: 4px">' . $this->l('This message will be divided in 160 chars parts') . '</div>') . '
						  </div>';
			}
			$code .= $this->displayFlags(Language::getLanguages(), $defaultLanguage, $this->_divLangName, 'SENDSMS_TXT_' . $hook_id, true);
		}
		$code .= '<div id="' . $hook_id . (($bAdmin) ? '_ADMIN' : '') . '" class="clear" style="padding-top: 4px; padding-bottom: 10px">'.$this->l('Variables you can use : ').' '.implode(', ', array_keys($values)).'</div>';
		$code .= '</div>';
		return $code;
	}

	private function _postValidation() {
	}
}
?>