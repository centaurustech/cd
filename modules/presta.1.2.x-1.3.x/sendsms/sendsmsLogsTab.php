<?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

include_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsGenericTab.php');
include_once(_PS_MODULE_DIR_.'/sendsms/sendsmsLogs.php');

class sendsmsLogsTab extends sendsmsGenericTab
{
	private $_sendsmsManager;

	public function __construct()
	{
		global $cookie;

	 	$this->table = 'sendsms_logs';
	 	$this->className = 'sendsmsLogs';
	 	$this->view = true;
	 	$this->delete = true;

		parent::__construct();

		$this->_sendsmsManager = new sendsmsManager();
		$this->_orderStates = OrderState::getOrderStates(intval($cookie->id_lang));


		$eventsArray = array();
		$manager = new sendsmsManager();
		$config = $manager->getConfig();
		foreach($config as $values) {
			if ($values[0] == 'postUpdateOrderStatus') {
				foreach ($this->_orderStates AS $state) {
					$eventsArray[$values[0] . '_' . $state['id_order_state']] = $manager->getLabel('postUpdateOrderStatus_short') . ' : ' . $state['name'];
				}
			} else
				$eventsArray[$values[0]] = $manager->getLabel($values[0]);
		}

		$this->fieldsDisplay = array(
		'id_sendsms_logs' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 40),
		//'id_customer' => array('title' => $this->l('Customer ID'), 'align' => 'center', 'width' => 60),
		'recipient' => array('title' => $this->l('Recipient'), 'width' => 180),
		//'phone' => array('title' => $this->l('Phone'), 'width' => 130),
		'event' => array('title' => $this->l('Event'), 'callback' => 'getEventLabel', 'widthColumn' => 337, 'type' => 'select', 'select' => $eventsArray, 'filter_key' => 'event', 'width' => 365),
		'date_add' => array('title' => $this->l('Date'), 'width' => 80),
		//'message' => array('title' => $this->l('Message'), 'width' => 200),
		//'nb_consumed' => array('title' => $this->l('Nb SMS'), 'align' => 'center', 'width' => 40, 'filter_key' => 'nb_consumed', 'filter_type' => 'int'),
		//'paid_by_customer' => array('title' => $this->l('Paid by customer'), 'align' => 'center', 'width' => 25),
		'simulation' => array('title' => $this->l('Simu'), 'align' => 'center', 'widthColumn' => 48, 'type' => 'bool', 'icon' => array(0 => 'disabled.gif', 1 => 'enabled.gif'), 'filter_key' => 'simulation', 'width' => 48),
		'status' => array('title' => $this->l('Sent'), 'align' => 'center', 'widthColumn' => 48, 'type' => 'bool', 'icon' => array(0 => 'disabled.gif', 1 => 'enabled.gif'), 'filter_key' => 'status', 'width' => 48));
		//'ticket' => array('title' => $this->l('Ticket'), 'width' => 40));
	}

	public function displayListHeader() {
		echo '
		<h2>'.$this->l('SendSMS Module - Message history').'</h2>
		<div style="clear:both;">&nbsp;</div>';

		parent::displayListHeader();
	}

	public function display()
	{
		global $cookie;

		if (isset($_GET['view'.$this->table]))
			$this->viewDetails();
		else
		{
			$this->getList(intval($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'date_add' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
			$this->displayList();
		}
	}

	public function viewDetails()
	{
		global $currentIndex, $cookie;

		$sendsmsLogs = $this->loadObject();

		echo '
			<h2>'.$this->l('Detail of SMS n ').$sendsmsLogs->id.'</h2>
			<br />
			<fieldset style="width: 400px; float: left">
				<legend><img src="../img/admin/tab-customers.gif"/> '.$this->l('Recipient information').'</legend>
				<table width="100%" cellspacing="5">
					<tr>
						<td align="right" style="width: 140px; font-weight: bold">' . $this->l('Recipient : ') . '</td>
						<td style="padding-left: 20px">' . ($sendsmsLogs->recipient == '--' ? $this->l('Admin') : $sendsmsLogs->recipient) . '</td>
					</tr>
					<tr>
						<td align="right" style="width: 140px; font-weight: bold">' . $this->l('Phone : ') . '</td>
						<td style="padding-left: 20px">' . $sendsmsLogs->phone . '</td>
					</tr>
					<tr>
						<td align="right" style="width: 140px; font-weight: bold">' . $this->l('Customer ID : ') . '</td>
						<td style="padding-left: 20px">' . (empty($sendsmsLogs->id_customer) ? '' : $sendsmsLogs->id_customer) . '</td>
					</tr>
					<tr>
						<td align="right" style="width: 140px; font-weight: bold"><br>' . $this->l('Paid for SMS : ') . '</td>
						<td style="padding-left: 20px"><br>' . ($sendsmsLogs->paid_by_customer == 1 ? $this->l('Yes') : $this->l('No')) . '</td>
					</tr>
				</table>
			</fieldset>
			<fieldset style="width: 440px; float: left; margin-left: 30px">
				<legend><img src="../modules/'.$this->_module.'/information.png"/> '.$this->l('SMS details').'</legend>
				<table width="100%" cellspacing="5">
					<tr>
						<td align="right" style="width: 140px; font-weight: bold">' . $this->l('Date : ') . '</td>
						<td style="padding-left: 20px">' . $sendsmsLogs->date_add . '</td>
					</tr>
					<tr>
						<td valign="top" align="right" style="font-weight: bold">' . $this->l('Event : ') . '</td>
						<td valign="top" style="padding-left: 20px">' . $sendsmsLogs->getEventLabel($sendsmsLogs->event) . '</td>
					</tr>
					<tr>
						<td align="right" style="font-weight: bold">' . $this->l('SMS used : ') . '</td>
						<td style="padding-left: 20px">' . $sendsmsLogs->nb_consumed . '</td>
					</tr>
					<tr>
						<td align="right" style="font-weight: bold">' . $this->l('Credits used : ') . '</td>
						<td style="padding-left: 20px">' . number_format($sendsmsLogs->credit, 3, ',', ' ') . ' ' . $this->l('euro') . '</td>
					</tr>
					<tr>
						<td align="right" style="font-weight: bold">' . $this->l('Status : ') . '</td>
						<td style="padding-left: 20px">' . ($sendsmsLogs->status==1 ? '<img src="../img/admin/enabled.gif"/>' : '<img src="../img/admin/disabled.gif"/>') . '</td>
					</tr>' .
					(($sendsmsLogs->simulation == 1) ?
					(($sendsmsLogs->status != 1) ? '
					<tr>
						<td align="right" style="font-weight: bold">' . $this->l('Error : ') . '</td>
						<td style="padding-left: 20px">' .  $this->_getErrorMessage($sendsmsLogs->error) . '</td>
					</tr>' : '') . '
					<tr>
						<td align="right" style="font-weight: bold">' . $this->l('Simulation : ') . '</td>
						<td style="padding-left: 20px"><img src="../img/admin/enabled.gif"/></td>
					</tr>' : '
					<tr>
						<td align="right" style="font-weight: bold">' . ($sendsmsLogs->status == 1 ? $this->l('Ticket : ') : $this->l('Error : ')) . '</td>
						<td style="padding-left: 20px">' . ($sendsmsLogs->status == 1 ? $sendsmsLogs->ticket : $this->_getErrorMessage($sendsmsLogs->error)) . '</td>
					</tr>') . '
				</table>
			</fieldset>
			<div style="clear: both"></div>
			<br /><br />
			<fieldset style="clear: both">
				<legend><img src="../modules/'.$this->_module.'/sendsmsSendTab.gif"/> '.$this->l('Message').'</legend>
				<div style="min-height: 50px; width: 900px; overflow: auto">' . nl2br(htmlentities($sendsmsLogs->message, ENT_COMPAT, 'UTF-8')) . '</div>
			</fieldset>
			<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
	}

	private function _getErrorMessage($error) {
		switch($error) {
			case 100:
				return $this->l('Can\'t connect to www.smsworldsender.com');
				break;
			case 101:
				return $this->l('Your message is empty');
				break;
			case 102:
				return $this->l('Sending message longer than 1 SMS is not authorized in your account settings');
				break;
			case 103:
				return $this->l('Bad recipient');
				break;
			case 104:
				return $this->l('Not enough credits on your account');
				break;
			case 105:
				return $this->l('Message is too long');
				break;
			case 105:
				return $this->l('Message is too long');
				break;
			case 110:
				return $this->l('Missing parameters');
				break;
			case 111:
				return $this->l('Invalid connection informations');
				break;
			default:
				return $this->l('Unknown error');
				break;
		}
	}
}
?>