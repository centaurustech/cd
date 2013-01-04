<?php

// 
if (!defined('_PS_VERSION_'))
     die(header('HTTP/1.0 404 Not Found'));

if (file_exists(dirname(__FILE__) . '/kekoliRequest.php'))
     require_once(dirname(__FILE__) . '/kekoliRequest.php');

/**
 * Connect Prestashop to KeKoli service
 * @version 1.0.0
 * @author 202-ecommerce
 */
class kekoli extends Module {

     /**
      * Configuration values
      * @var array 
      */
     private $Config;

     /**
      * Default link
      * @var string 
      */
     private $Link;

     /**
      * Number of log by page
      * @var int 
      */
     private $LogsByPage = 50;

     /**
      * Color default
      * @var string 
      */
     private $defaultColor = "color:green;";

     /**
      * Constructor module
      * @version 1.0.0
      */
     public function __construct() {
          global $cookie;

          $this->name = 'kekoli';
		  $this->tab = 'shipping_logistics';
          $this->version = '1.1.0';
          $this->author = '202 ecommerce';
		  $this->module_key = "abc696e0aa79a6ea402216f8bc1c1e41";
          parent::__construct();
          $this->displayName = $this->l('KeKoli');
          $this->description = $this->l('Connect Prestashop to KeKoli service');

          $this->_getConfiguration();

		  if(isset($cookie))
				{
				$token = Tools::getAdminToken('AdminModules' . intval(Tab::getIdFromClassName('AdminModules')) . intval($cookie->id_employee));
				$this->Link = 'index.php?tab=AdminModules&configure=' . $this->name . '&token=' . $token . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
				}

          if (!isset($this->Config['KeKoli_Consumer_key']))
               $this->warning = $this->l('KeKoli module is not configured, please enter credentials.');
     }

     /////////////////////////////////////////:
     // Install / Uninstall

     /**
      * Installing the module
      * @version 1.0.0
      * @return boolean 
      */
     public function install() {
          if (parent::install() === false
                  OR $this->SQLInstall() === false
                  OR $this->registerHook("backOfficeTop") === false
                  OR Configuration::updateValue("KeKoli_DateInstall", strftime('%Y-%m-%d %H:%M:%S')) === false
          )
               return false;

          return true;
     }

     /**
      * Create SQL 
      * @version 1.0.0
      * @return boolean 
      */
     private function SQLInstall() {
          // Table logs
          $MySQLQuery = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->name . '_log` (
                              `id_log` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                              `date_start` TIMESTAMP,
                              `date_end` TIMESTAMP,
                              `trigger` VARCHAR(1) NOT NULL,
                              `log` TEXT
                         ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
          if (!Db::getInstance()->Execute($MySQLQuery))
               return false;

          $MySQLQuery = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->name . '_order` (
                              `id_order` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                              `date` TIMESTAMP NOT NULL,
                              `tracking_code` VARCHAR(255) NOT NULL,
                              `statut` VARCHAR(255) NOT NULL
                         ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
          if (!Db::getInstance()->Execute($MySQLQuery))
               return false;

          return true;
     }

     /**
      * Uninstalling the module
      * @version 1.0.0
      * @return boolean 
      */
     public function uninstall() {
          if (Configuration::deleteByName('KeKoli_Consumer_key') === false
                  OR Configuration::deleteByName('KeKoli_Consumer_secret') === false
                  OR Configuration::deleteByName('KeKoli_Token') === false
                  OR Configuration::deleteByName('KeKoli_Token_secret') === false
                  OR Configuration::deleteByName('KeKoli_Feedback') === false
                  OR Configuration::deleteByName('KeKoli_DateInstall') === false
				  OR Configuration::deleteByName('KeKoli_SourceStatus') === false
				  OR Configuration::deleteByName('KeKoli_DestinationStatus') === false
				  OR $this->SQLUninstall() === false
                  OR parent::uninstall() === false)
               return false;
          return true;
     }

     /**
      * Remove SQL
      * @return boolean 
      */
     private function SQLUninstall() {
          $MySQLQuery = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $this->name . '_log`';
          if (!Db::getInstance()->Execute($MySQLQuery))
               return false;
          $MySQLQuery = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $this->name . '_order`';
          if (!Db::getInstance()->Execute($MySQLQuery))
               return false;

          return true;
     }

     /////////////////////////////////////////:
     // Conf

     /**
      * Administration panel
      * @version 1.0.0
      * @return string 
      */
     public function getContent() {
          if (!extension_loaded('curl') || !ini_get('allow_url_fopen')) {
               $this->_html = '<center>' . parent::displayError("You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.") . '</center>';
               return $this->_html;
          }
          $this->_html .= '
               
               <fieldset id="wrapped">';
          if (Tools::isSubmit('connectionTest')) {
               $this->_postValidation();
               // If there are no errors
               if (!isset($this->_postErrors)) {
                    $this->_postProcess();
                    $this->_getConfiguration();
                    // If connection test
                    if (Tools::isSubmit('connectionTest'))
                         $this->ping();
               } else {
                    // Display errors
                    foreach ($this->_postErrors AS $err)
                         $this->_html .= parent::displayError($err);
               }
          } else if (Tools::getValue('SendToKekoli'))
               $this->SendOrders();

          if (!isset($this->Config['KeKoli_Consumer_key']) OR Tools::getValue('FormConfig') OR Tools::isSubmit('connectionTest')) {
               $this->_displayFormConfig();
          } else if (Tools::getValue('DisplayLogs')) {
			   $this->_html .= $this->_getHotLine();
               $this->_displayHomeButton();
               $this->_displayLogsOrder();
          } else {
			   $this->_html .= $this->_getHotLine();
               $this->_displayConfigButton();
               $this->_displayOrders();
               $this->_displayLog();
          }
          $this->_html .= '</fieldset>';
          return $this->_html;
     }

     /**
      * Configuration form
      * @version 1.0.0 
      */
     private function _displayFormConfig() {
		  global $cookie;
		  $this->_html .= $this->_getHotLine();
	      if($this->_getNBLogs() > 0)
			$this->_html .= $this->_displayHomeButton() . '<h2>' . $this->l('KeKoli module configuration') . '</h2>';
		  else
			$this->_html .='<h2>' . $this->l('Welcome to the KeKoli module') . '</h2>';			

          $this->_html .='
                    <p> 
                         ' . $this->l('This module will help you to synchronize your Prestashop site with KeKoli dashboard.') . '
                         ' . $this->l('You can thus have a global vision of orders delivery directly from your KeKoli dashboard.') . '
                    </p>
                    <form action="' . Tools::htmlentitiesUTF8($this->Link) . '" method="post" style="clear:both;">
                         <table style="border: 1px solid #555; width: 48%; float: left; margin-top: 15px;" cellpadding="0" cellspacing="0" id="table2">
                              <thead>
                                   <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #555;padding: 10px  0 10px 10px "><h3 style="margin:0;">' . $this->l('Already registered to KeKoli ?') . '</h3></td>
                                   </tr>
                              </thead>
                              <tbody>
								   <tr>
										<td colspan="2" style="height: 35px; padding: 10px 10px 0px 10px;">'.$this->l('Go to').' <a href="http://www.kekoli.com/user" target="_blank" style="color:green;">'.$this->l('my account on KeKoli.com').'</a>'.$this->l(', and choose "partenaires EC" then "activate".').'</td>
								   </tr>
								   <tr>
										<td colspan="2" style="height: 35px; padding: 0px; text-align:center;"><b>'.$this->l('Credentials').'</b></td>
								   </tr>
                                   <tr>
                                        <td style="height: 35px;padding: 0px; padding-left: 10px;">' . $this->l('Consumer key') . '</td>
                                        <td><input type="text" name="Consumer_key" value="' . Tools::htmlentitiesUTF8($this->Config['KeKoli_Consumer_key']) . '" size="40" /></td>
                                   </tr>
                                   <tr>
                                        <td style="height: 35px;padding: 0px; padding-left: 10px;">' . $this->l('Consumer secret') . '</td>
                                        <td><input type="text" name="Consumer_secret" value="' . Tools::htmlentitiesUTF8($this->Config['KeKoli_Consumer_secret']) . '" size="40" /></td>
                                   </tr>
                                   <tr>
                                        <td style="height: 35px;padding: 0px; padding-left: 10px;">' . $this->l('Token') . '</td>
                                        <td><input type="text" name="Token" value="' . Tools::htmlentitiesUTF8($this->Config['KeKoli_Token']) . '" size="40" /></td>
                                   </tr>
                                   <tr>
                                        <td style="height: 35px;padding: 0px; padding-left: 10px;">' . $this->l('Token secret') . '</td>
                                        <td><input type="text" name="Token_secret" value="' . Tools::htmlentitiesUTF8($this->Config['KeKoli_Token_secret']) . '" size="40" /></td>
                                   </tr>
   								   <tr>
										<td colspan="2" style="height: 35px; padding: 0px; text-align:center;"><b>'.$this->l('Configuration').'</b></td>
								   </tr>
                                   <tr>
                                        <td colspan="2" style="height: 35px; padding: 0px; padding-left: 10px;">' . $this->l('Activate feedback to customer : ') . '<input type="checkbox" name="Feedback" ' . ($this->Config['KeKoli_Feedback'] ? 'checked' : '') . '/></td>
                                   </tr>
								   <tr>
                                        <td style="height: 35px;padding: 0px; padding-left: 10px;">' . $this->l('Source status') . '</td>
                                        <td><select name="SourceStatus">'.$this->getSelectStates($this->Config['KeKoli_Consumer_key'] ? $this->Config['KeKoli_SourceStatus'] : '4').'</select></td>
                                   </tr>
								   <tr>
                                        <td style="height: 35px;padding: 0px; padding-left: 10px;">' . $this->l('Destination status') . '</td>
                                        <td><select name="DestinationStatus">'.$this->getSelectStates($this->Config['KeKoli_Consumer_key'] ? $this->Config['KeKoli_DestinationStatus'] : '5').'</select></td>
                                   </tr>
                                   <tr>
                                        <td colspan="2" style="height: 35px; padding: 5px 0; padding-left: 10px;" align="center">';
		  if($this->Config['KeKoli_Consumer_key']!='')
			$this->_html .='
										<a style="vertical-align:middle;margin: 15px;" href="' . $this->Link . '" class="button">' . $this->l('Cancel') . '</a>';
		  $this->_html .='
										<input class="button" name="connectionTest" value="' . $this->l('Update settings and test connection') . '" type="submit" /></td>
                                   </tr>
                              </tbody>
                         </table>
                         <table style="border: 1px solid #555; width: 48%; float: right; margin-top: 15px;" cellpadding="0" cellspacing="0" id="table1">
                              <thead>
                                   <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #555;padding: 10px  0 10px 10px "><h3 style="margin:0;">' . $this->l('Not yet a KeKoli customer ?') . '</h3></td>
                                   </tr>
                              </thead>
                              <tbody>
                                   <tr>
                                        <td>
                                             <ul style="margin-right: 20px;text-align:justify;">
                                                  <li style="margin: 10px 0;">' . $this->l('KeKoli is a solution that helps you track the delivery of your shipment to your customers. It helps you identify over-delay shipment and save up to 12% of your turnover !') . ' </li>
                                                  <li style="margin: 10px 0;">' . $this->l('KeKoli require a subscription plan - without any duration commitment - depending on your monthly volume of shipments.') . '</li>
                                                  <li style="margin: 10px 0;">' . $this->l('Subscribe at').' <a href="'.$this->l('http://www.kekoli.com/inscription').'" target="_blank" style="color:green;">'.$this->l('http://www.kekoli.com/inscription').'</a> '.$this->l('to get the welcome pack. Once subscribe, just to to "my account" section to get your Prestashop credentials.') . '</li>
                                             </ul>
                                        </td>
                                   </tr>
                              </tbody>
                              <tfooter>
                                   <tr>
                                        <td style="text-align:center;padding: 10px;">
                                             <a href="http://www.kekoli.com" target="_blank" class="button"> ' . $this->l('Free try') . '</a>
                                        </td>
                                   </tr>
                              </tfooter>
                         </table>
                    </form>';
     }

     /**
      * Post validation
      * @version 1.0.0 
      */
     private function _postValidation() {
          if (Tools::isSubmit('connectionTest')) {
               if (!Tools::getValue('Consumer_key'))
                    $this->_postErrors[] = $this->l('Customer key field is required.');
               else if (!Tools::getValue('Consumer_secret'))
                    $this->_postErrors[] = $this->l('Customer secret field is required.');
               else if (!Tools::getValue('Token'))
                    $this->_postErrors[] = $this->l('Token field is required.');
               else if (!Tools::getValue('Token_secret'))
                    $this->_postErrors[] = $this->l('Token secret field is required.');
          }
     }

     /**
      * Post process
      * @version 1.0.0 
      */
     private function _postProcess() {
          Configuration::updateValue('KeKoli_Consumer_key', Tools::getValue('Consumer_key'));
          Configuration::updateValue('KeKoli_Consumer_secret', Tools::getValue('Consumer_secret'));
          Configuration::updateValue('KeKoli_Token', Tools::getValue('Token'));
          Configuration::updateValue('KeKoli_Token_secret', Tools::getValue('Token_secret'));
          Configuration::updateValue('KeKoli_Feedback', Tools::getValue('Feedback'));
		  Configuration::updateValue('KeKoli_SourceStatus', Tools::getValue('SourceStatus'));
		  Configuration::updateValue('KeKoli_DestinationStatus', Tools::getValue('DestinationStatus'));

          $this->_html .= parent::displayConfirmation($this->l('Settings updated'));
     }

	 /**
	  * sends back available states as <options> with selected option as parameter
	  * @version 1.0.0
	  */
	 private function getSelectStates($selectedStateID = '') {
		  global $cookie;
		  $states = OrderState::getOrderStates((int)($cookie->id_lang));
		  $selectStates = '<option value="">-- ' . $this->l('No order status update') . ' --</option>';
		  foreach($states as $state)
			{
			if($selectedStateID==$state['id_order_state'])
				$option = 'selected';
			else
				$option = '';
			$selectStates .= '<option value="'.$state['id_order_state'].'" '.$option.'>'.$state['name'].'</option>';
			}
		  return $selectStates;
	 }
	 
     /**
      * Display configuration button
      * @version 1.0.0 
      */
     private function _displayConfigButton() {
          $this->_html .= '
               <div style="margin-top: 5px;margin-right: 15px;text-align: right;float: right;">
                    <a href="' . $this->Link . '&FormConfig=true" class="button">' . $this->l('Configuration') . '</a>
               </div>';
     }
	 
     /**
      * Display home button
      * @version 1.0.0 
      */
     private function _displayHomeButton() {
          $this->_html .= '
               <div style="margin-top: 5px;margin-right: 15px;text-align: right;float: right;">
                    <a href="' . $this->Link . '" class="button">&lt; ' . $this->l('Home') . '</a>
               </div>';
     }

     /**
      * Display log
      * @version 1.0.0 
      */
     private function _displayLog() {
          $this->_html .=
                  '<fieldset style="font-size:inherit;">
			<legend><img src="../img/admin/contact.gif" />' . $this->l('Last sending logs') . '</legend>';
          $logs = $this->_getLogTable(0, 5);
          if ($logs == '') {
               $this->_html .= $this->l('No log yet');
          } else {
               $this->_html .= $logs;
               if ($this->_getNBLogs() > 5)
                    $this->_html .= '<br><a href="' . $this->Link . '&DisplayLogs=true" class="button">' . $this->l('Previous logs') . '</a>';
          }
          $this->_html .= '</fieldset>';
     }

     /**
      * Create a tables containing logs
      * @param start : first log to show
      * @param number : number of resultst to show
      * return text
      * @version 1.0.0
      */
     private function _getLogTable($start, $number) {
          $MySQLQuery = "SELECT * FROM `" . _DB_PREFIX_ . $this->name . "_log` ORDER BY `date_start` DESC LIMIT " . $start . ", " . $number;
          $res = Db::getInstance()->ExecuteS($MySQLQuery);
          if (mysql_affected_rows() == 0)
               return '';

          $html = '<table style="width:100%;margin: 0 auto;" border=1 cellspacing=0 cellpadding=0>';
          foreach ($res as $logs) {
               $html .= '<tr>
                                   <td style="width:140px;padding: 5px;">' . $logs['date_start'] . '</td>
                                   <td style="width:90px;padding: 5px;">' . ($logs['trigger'] == 'C' ? 'Cron' : $this->l('Manual') ) . '</td>
                                   <td style="padding: 5px;">' . $logs['log'] . '</td>
                             </tr>';
          }
          return $html . '</table>';
     }

     /**
      * count logs
      * return integer
      * @version 1.0.0
      */
     private function _getNBLogs() {
          $MySQLQuery = "SELECT COUNT(`id_log`) FROM `" . _DB_PREFIX_ . $this->name . "_log`";
          return Db::getInstance()->getValue($MySQLQuery);
     }

     /**
      * Return configuration
      * @version 1.0.0
      */
     private function _getConfiguration() {
          $this->Config = Configuration::getMultiple(array('KeKoli_Consumer_key', 'KeKoli_Consumer_secret', 'KeKoli_Token', 'KeKoli_Token_secret', 'KeKoli_Feedback', 'KeKoli_DateInstall', 'KeKoli_SourceStatus', 'KeKoli_DestinationStatus'));
     }

     /**
      * Get the number of command to send
      * @version 1.0.0
      * @return int 
      */
     private function _getNbOrdersToSend() {
          $MySQLQuery = "SELECT COUNT(`id_order`) FROM `" . _DB_PREFIX_ . "orders` WHERE `id_order` NOT IN (SELECT `id_order` FROM `" . _DB_PREFIX_ . $this->name . "_order`) AND `date_upd` > '" . $this->Config['KeKoli_DateInstall'] . "' AND `shipping_number` != '' ";
          return Db::getInstance()->getValue($MySQLQuery);
     }

     /**
      * Get the orders
      * @version 1.0.0
      * @return array 
      */
     private function _getOrdersToSend() {
          $MySQLQuery = "
               SELECT o.`id_order`, o.`shipping_number`, c.`email`, a.`firstname`, a.`lastname`, a.`postcode`, a.`address1`, a.`address2`, a.`city`, p.`iso_code`
               FROM `" . _DB_PREFIX_ . "orders` AS o 
                    INNER JOIN `" . _DB_PREFIX_ . "customer` AS c
                         ON c.`id_customer` = o.`id_customer`
                    INNER JOIN `" . _DB_PREFIX_ . "address` AS a
                         ON a.`id_address` = o.`id_address_delivery`
                    INNER JOIN `" . _DB_PREFIX_ . "country` AS p
                         ON p.`id_country` = a.`id_country`
               WHERE o.`id_order` NOT IN (SELECT `id_order` FROM `" . _DB_PREFIX_ . $this->name . "_order`) AND o.`date_upd` > '" . $this->Config['KeKoli_DateInstall'] . "' AND o.`shipping_number` != '' 
                    ORDER BY o.`id_order` ASC";
          return Db::getInstance()->ExecuteS($MySQLQuery);
     }

     /**
      * Num of hotline
      * @return string 
      */
     private function _getHotLine() {
          return '
                    <div style="background:#DDD;' . $this->defaultColor . ' border-radius: 15px; float:right; width: 215px;text-align:center;padding-left: 10px;line-height: 1.5em;"><div style="padding:5px;">
                    HOTLINE : +33 (0)1 84 16 55 70<br>
					<a href="http://www.kekoli.com" target="_blank" style="' . $this->defaultColor . '">KeKoli.com</a>
                    </div></div>';
     }

     /**
      * Sending orders
      * @version 1.0.0 
      */
     private function _displayOrders() {
          $this->_html .= '
               <h2><a href="' . $this->Link . '" style="color:inherit;">' . $this->displayName . '</a></h2>
                    <h3>' . $this->l('Click the').' "'.$this->l('Send').'" '.$this->l('button to send your shipped orders to KeKoli') . '</h3>
                    
					<p>
                         ' . $this->l('Below is shown number of orders shipped to customers that were not sent to KeKoli since last sending.') . '<br />
                    </p>
                    <p style="color: grey;margin: 10px 0;">
                         ' . $this->l('Note: when installing the module, there is no orders to send because only orders shipped after the installation date are taken into account.') . '
                    </p>
                    <fieldset style="margin-bottom:15px;font-size:inherit;clear:both;">
                         <legend><img src="../img/admin/contact.gif" />' . $this->l('Command sending to KeKoli') . '</legend>
                         <form method="POST" action="' . $this->Link . '">
							  <div style="padding-top:50px;float: left;">
							  <div style="background:#DDD; border-radius: 15px;">
                              <table id="table1" style="margin: 0 auto;padding:15px;">
                                   <tr>
                                        <td style="width:260px;">
                                             <span style="' . $this->defaultColor . '">' . $this->l('Pending orders :') . ' ' . $this->_getNbOrdersToSend() . '</span>
                                        </td>
                                        <td>
                                             <input type="submit" name="SendToKekoli" value="' . $this->l('Send') . '" class="button" />
                                        </td>
                                   </tr>
                              </table>
							  </div></div>
                              <table id="table2" style="float:right; width: 38%; border: 1px solid #555; border-radius: 20px;" cellspacing=0 cellpadding=0>
                                   <thead>
                                        <tr>
                                             <th style="border-bottom: 1px solid #555;padding: 10px;">
                                                  <span style="color:green">' . $this->l('Tip :') . '</span>
                                                  ' . $this->l('Automate sendings') . '
                                             </th>
                                        </tr>
                                   </thead>
                                   <tbody>
                                        <tr>
                                             <td style="padding: 10px;">
                                                  <p>
                                                       ' . $this->l('You can automate order sending to Kekoli by creating a CRON task  for : ') . '
													   '.dirname(__FILE__).'/kekoliCronSend.php
													   <br /><br />
                                                       ' . $this->l('Ask your webmaster or') . '
                                                       <a href="http://www.kekoli.com" style="' . $this->defaultColor . '" target="_blank">' . $this->l('call our technical support') . '</a>
                                                  </p>
                                             </td>
                                        </tr>
                                   </tbody>
                              </table>
                         </form>
                    </fieldset>';
     }

     /**
      * Displays logs
      */
     private function _displayLogsOrder() {
          // Pagination
          if (!Tools::getValue('Page')) {
               $start = 0;
               $page = 1;
          } else {
               $start = (Tools::getValue('Page') - 1) * $this->LogsByPage;
               $page = Tools::getValue('Page');
          }

          $count = $this->_getNBLogs();

          $MySQLQuery = "SELECT * FROM `" . _DB_PREFIX_ . $this->name . "_log` ORDER BY `date_start` DESC LIMIT " . $start . ", " . $this->LogsByPage;
          $res = Db::getInstance()->ExecuteS($MySQLQuery);

          $this->_html .= '
					<h2><a href="' . $this->Link . '" style="color:inherit;">' . $this->displayName . '</a></h2>
                    <fieldset style="margin-bottom: 15px;font-size:inherit;clear:both;">
                         <legend><img src="../img/admin/contact.gif" />' . $this->l('Sending log') . '</legend>
                         <table style="width:100%;margin: 0 auto;" border=1 cellspacing=0 cellpadding=0>';
          foreach ($res as $logs) {
               $this->_html .= '
                              <tr>
                                   <td style="width:140px;padding: 5px;">' . $logs['date_start'] . '</td>
                                   <td style="width:90px;padding: 5px;">' . ($logs['trigger'] == 'B' ? 'Batch' : 'Manual' ) . '</td>
                                   <td style="padding: 5px;">' . $logs['log'] . '</td>
                              </tr>';
          }
          // Pagination
          if ($count > $this->LogsByPage) {
               $this->_html .= '
                    <tr>
                         <td colspan="3" style="padding: 5px;"><div style="float:left;">' . (Tools::getValue('Page') && Tools::getValue('Page') >= 2 ? '<a href="' . $this->Link . '&DisplayLogs=true&Page=' . ($page - 1) . '">&lt; Previous' : '' ) . '</div><div style="float:right;">' . ((Tools::getValue('Page') + 1) <= floor($count / $this->LogsByPage) ? '<a href="' . $this->Link . '&DisplayLogs=true&Page=' . ($page + 1) . '">Next &gt;</a>' : '' ) . '</div></td>
                    </tr>';
          }

          $this->_html .= '
                         </table>
                    </fieldset>
                    <a class="button" style="margin: 15px;" href="' . $this->Link . '">&lt; ' . $this->l('Home') . '</a>';
     }

     /////////////////////////////////////////:
     // Hook

     /**
      * @version 1.0.0
      */
     public function hookBackOfficeTop() {
          if (Tools::getValue('tab') && Tools::getValue('tab') == 'AdminOrders') {
               echo '
               <script type="text/javascript">
                    $(function(){
                         $("#submenu").append("<li><a href=\"' . $this->Link . '\">'.$this->l('KeKoli module').' (' . $this->_getNbOrdersToSend() . ')</a></li>");
                         $("#submenu").append("<li><a href=\"http://www.kekoli.com\" target=\"_blank\">'.$this->l('KeKoli website').'</a></li>");
                    });
               </script>
                    ';
          }
     }

     /////////////////////////////////////////:
     // Webservice

     /**
      * Test connexion
      * @return text 
      */
     private function ping() {
          $res = kekoliRequest::ping($this->Config);
          //$this->_html = print_r($res, true);
          if ($res[0] == 200) {
               if ($res[1]['ok'] == 1)
					{
					$message = $this->l('Connexion successfull for account : ').$res[1]['who'];
					if($this->_getNBLogs() <= 0)
						$message .= ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $this->Link . '">'.$this->l('next step').' &gt; </a>';
                    $this->_html .= parent::displayConfirmation($message);
					}
               else
                    $this->_html .= parent::displayError($this->l('Connexion successfull, but wrong answer, please contact provider.'));
          }
          elseif ($res[0] == 401)
               $this->_html .= parent::displayError($this->l('Connexion fail due to authentication, please check credentials'));
          elseif ($res[0] == 0)
               $this->_html .= parent::displayError($this->l('Connexion fail : can\'t connect host ') . $res[2]);
          else
               $this->_html .= parent::displayError($this->l('Connexion fail : unknown error ').'('.$res[0].')');
     }

     /**
      * Send order
      * @return text
      */
     public function SendOrders($trigger = 'M') {
          $MySQLQuery = 'INSERT INTO `' . _DB_PREFIX_ . $this->name . '_log` (`date_start`, `trigger`, `log`) VALUES (NOW(), \'' . $trigger . '\', \'If you see this message, script did not finised correctly\') ';
          Db::getInstance()->Execute($MySQLQuery);
          $log_id = DB::getInstance()->Insert_ID();

		  ///////////////////////// 1 : Order Sending
          $faillist = '';
          $fail = 0;
		  $NbOrderSent = 0;
          $NbOrderTreated = 0;
		  $orders_csv = '';
          $orders = $this->_getOrdersToSend();
		  $errorMessage = '';
		  //echo 'count($this->_getOrdersToSend()) : '.count($orders).', $this->_getNbOrdersToSend() : '.$this->_getNbOrdersToSend();
          $track = array();
          $res = DB::getInstance()->ExecuteS("SELECT `id_order`, `tracking_code` FROM " . _DB_PREFIX_ . $this->name . "_order ORDER BY id_order ASC");
          foreach ($res as $order) {
               $track[$order['tracking_code']] = $order['id_order'];
          }
          foreach ($orders as $order) {
			   //echo '<br>id_order : '.$order['id_order'];
               if (!key_exists($order['shipping_number'], $track)) {
					$NbOrderSent ++;
					//echo 'SENDING TO CSV';
                    $track[$order['shipping_number']] = $order['id_order'];
                    $orders_csv .= $order['shipping_number'] . ';' . strftime('%d/%m/%Y') . ';' . $order['firstname'] . ' ' . $order['lastname'] . ';' . $order['city'] . ';' . $order['postcode'] . ';' . $order['iso_code'] . ';' . $order['address1'] . ';' . $order['address2'] . ';';
                    if ($this->Config['KeKoli_Feedback'] == 'on')
                         $orders_csv .= $order['email'];
                    $orders_csv .= "\r\n";
               }
               else {
					//echo 'INSERT';
                    DB::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . $this->name . '_order` (`id_order`, `tracking_code`, `statut`) VALUES (' . $order['id_order'] . ', "' . $order['shipping_number'] . '", 0)');
                    $faillist .= $order['shipping_number'] . ' '.$this->l('is used in more than one order in your shop, duplicate order is number').' '.$track[$order['shipping_number']] . ', ';
                    //$fail++;
               }
          }

          if ($orders_csv != '') {
			   //echo '<br>Sending CSV ';
			   $orders_csv = 'NumeroColis;DateClotureBordereau;NomDestinataire;Commune;CodePostal;CodePays;Adresse1;Adresse2;Email' . "\r\n".$orders_csv;
               $res = kekoliRequest::SendOrders($this->Config, $orders_csv);
			   //echo '$res : '.$res[0];
               if ($res[0] == 200) {
                    $success = 0;
					// if $res[1] == '') + ajouter sucess -1
					if($res[1]=='')
						{ $errorMessage = $this->l('KeKoli answer empty, cannot update sent orders'); }
					else
						{
						foreach ($res[1] as $line) {
							 //echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;$line : '.print_r($line);
							 $NbOrderTreated++;
							 if ($line['ok'] == 1) {
								  $success++;
							 } else {
								  $fail++;
								  if($line['msg'] == 'Duplicate tracking number')
									{ $faillist .= $line['id'].' '.$this->l('is already used for another order in KeKoli database').', '; }
								  else
									{ $faillist .= $line['id'] . ' : ' . $line['msg'] . ', '; }
							 }
							 $id = DB::getInstance()->ExecuteS('SELECT `id_order` FROM `' . _DB_PREFIX_ . 'orders` WHERE `shipping_number`=\'' . pSQL($line['id']) . '\' ORDER BY `id_order` ASC LIMIT 1');
							 $MySQLQuery = 'INSERT INTO `' . _DB_PREFIX_ . $this->name . '_order` (`id_order`, `tracking_code`, `statut`) VALUES (';
							 $MySQLQuery.= $id[0]['id_order'] . ', \'' . pSQL($line['id']) . '\',\'' . pSQL($line['ok']) . '\') ';
							 if (!Db::getInstance()->Execute($MySQLQuery))
								  if ($trigger != 'C')
									   $errorMessage = $this->l('Error while saving order submission ') . $MySQLQuery;
								  elseif (mysql_affected_rows() > 1)
									   if ($trigger != 'C')
											$errorMessage = $this->l('Error while saving order submission ') . ' : ' . $this->l('more than one order is associated with tracking number ') . $line['id'];
									   elseif (mysql_affected_rows() < 1)
											if ($trigger != 'C')
												 $errorMessage = $this->l('Error while saving order submission ') . ' : ' . $this->l('tracking number not found ') . $line['id'];
							}
						$message = $this->l('Update done').' ; '.$this->l('Order').' : '.count($orders).', '.$this->l('sent').' : '.$NbOrderSent.', '.$this->l('treated').' : '.$NbOrderTreated.', '.$this->l('success'). ' : '.$success.', '.$this->l('fail'). ' : '.$fail;
						}
               } elseif ($res[0] == 401) {
                    $errorMessage = 'Connexion fail due to authentication, please check credentials';
               } elseif ($res[0] == 0) {
                    $errorMessage = 'Connexion fail : can\'t connect host';
                    $errorMessage .= ' ' . $res[2];
               } else {
                    $errorMessage = 'Connexion fail : unknown error, please contact provider.';
               }
          } else {
               $message = $this->l('No order to send');
          }
		  if($faillist=='')
				{ $faillist = ', '; }
		  else
				{ $faillist = ' ('.substr($faillist, 0, -2).'), '; }

		  ///////////////////////// 2 : Order Update
		  if($this->Config['KeKoli_SourceStatus']=='')
				$messagew = $this->l('status update desactivated.');
		  else
				{
				  $messagew = '';
				  $res = kekoliRequest::GetOrders($this->Config);
				  if ($res[0] == 200) {
					  $count = 0;
					  $orderSelect = '';
					  $updatedOrders = 0;
					  foreach($res[1] as $MyOrder)
							{
							//echo '$MyOrder : '.print_r($MyOrder, true).'<br>';
							if($MyOrder['status']==6)
								{
								$count++;
								$orderSelect .= '\''.$MyOrder['id'].'\', ';
								}
							if($count==40)
								{
								$updatedOrders += $this->updateStatus($orderSelect);
								$count = 0;
								$orderSelect = '';
								}
							}
					  if($count!=0)
							$updatedOrders += $this->updateStatus($orderSelect);
					  
					  if($updatedOrders==0)
							$messagew = $this->l('no status update received.');
					  elseif($updatedOrders==1)
							$messagew = $this->l('1 status update done.');
					  else
							$messagew = $updatedOrders.' '.$this->l('status update done.');
					  
				  } elseif ($res[0] == 401) {
							$errorMessage = 'Connexion fail due to authentication, please check credentials';
				  } elseif ($res[0] == 0) {
							$errorMessage = 'Connexion fail : can\'t connect host';
							$errorMessage .= ' ' . $res[2];
				  } else {
							$errorMessage = 'Connexion fail : unknown error, please contact provider.';
				  }
				}

		  if ($trigger != 'C')
				{
				if($errorMessage != '')
					$this->_html .= parent::displayError($errorMessage);
				if($message != '')
					$this->_html .= parent::displayConfirmation($message.', '.$messagew);
				}
		  
		  if($errorMessage != '')
				$errorMessage .= '<br>';
		  $MySQLQuery = 'UPDATE `' . _DB_PREFIX_ . $this->name . '_log` SET `date_end`=NOW(), `log`=\'' . addslashes($message.$faillist.$messagew.$errorMessage) . '\' WHERE id_log=' . $log_id;
          Db::getInstance()->Execute($MySQLQuery);
     }

	 /**
      * Update status of orders (if status OK) based on tracking number
      * @return number of updates
      */
	 private function updateStatus($shippingNumberList) {
		global $cookie;
		if($shippingNumberList=='')
			return 0;
		$shippingNumberList = substr($shippingNumberList, 0, -2);
		$count=0;
	
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT id_order
		FROM '._DB_PREFIX_.'orders o
		WHERE '.(int)$this->Config['KeKoli_SourceStatus'].' = (
			SELECT id_order_state
			FROM '._DB_PREFIX_.'order_history oh
			WHERE oh.id_order = o .id_order
			ORDER BY date_add DESC, id_order_history DESC
			LIMIT 1
		) AND `shipping_number` in ('.$shippingNumberList.')
		ORDER BY invoice_date ASC');
		
		foreach($result as $orderIdToUpdate)
			{
			$history = new OrderHistory();
			$history->id_order = (int)$orderIdToUpdate['id_order'];
			if(isset($cookie))
				$history->id_employee = (int)($cookie->id_employee);
			$history->changeIdOrderState((int)($this->Config['KeKoli_DestinationStatus']), (int)($orderIdToUpdate['id_order']));
			$result = $history->add();
			$count += $result;
			}
		return $count;
	 }
	 
}
