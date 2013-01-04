<?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

include_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsGenericTab.php');

class sendsmsStatsTab extends sendsmsGenericTab
{
	public function display()
	{
		$this->_displayHeader();
		$this->_displayContent();
		echo $this->_html;
 	}

	private function _displayHeader()
	{
		$this->_html .= '
		<h2>'.$this->l('SendSMS Module - Statistics').'</h2>
		<div style="clear:both;">&nbsp;</div>';
	}

 	private function _displayContent()
	{
		global $cookie;

		$total = $this->_getTotal();
		$states = $this->_getTotalByState();
		$admin = $this->_getTotalForAdmin();
		$customer = $this->_getTotalForCustomer();
		$free = $this->_getTotalFree();
		$paid = $this->_getTotalPaidByCustomer();
		$average = $this->_getAverageCost();
		$orders = $this->_getOrdersWithSMS();
		$totalOrders = $this->_getNbTotalOrders();

		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
      			<legend><img src="../modules/'.$this->_module.'/sendsmsStatsTab.gif" /> '.$this->l('Statistics').'</legend>
				<table style="width: 100%; text-align: center">
					<tr style="font-weight: bold; background-color: #F4E6C9; height: 30px">
						<td style="width: 33%">'.$this->l('Number of messages sent').'</td>
						<td style="width: 33%">'.$this->l('Status').' <font color="green">'.$this->l('OK').'</font></td>
						<td style="width: 33%">'.$this->l('Status').' <font color="red">'.$this->l('NOK').'</font></td>
					</tr>
					<tr>
						<td>' . $total . '</td>
						<td>' . (isset($states[1]) ? $states[1] : 0) . ' (' . ($total <= 0 ? '' : round($states[1] / $total * 100, 2)) . '%)</td>
						<td>' . (isset($states[0]) ? $states[0] : 0) . ' (' . ($total <= 0 ? '' : round($states[0] / $total * 100, 2)) . '%)</td>
					</tr>
				</table>
				<table style="width: 100%; padding-top: 20px; text-align: center">
					<tr style="font-weight: bold; background-color: #F4E6C9; height: 30px">
						<td style="width: 33%">'.$this->l('Sent to the admin').'</td>
						<td style="width: 33%">'.$this->l('Sent to customers').'</td>
						<td style="width: 33%">'.$this->l('Freehand').'</td>
					</tr>
					<tr>
						<td>' . $admin . ' (' . ($total <= 0 ? '' : round($admin / $total * 100, 2)) . '%)</td>
						<td>' . $customer . ' (' . ($total <= 0 ? '' : round($customer / $total * 100, 2)) . '%)</td>
						<td>' . $free . ' (' . ($total <= 0 ? '' : round($free / $total * 100, 2)) . '%)</td>
					</tr>
				</table>
				<table style="width: 100%; padding-top: 20px; text-align: center">
					<tr style="font-weight: bold; background-color: #F4E6C9; height: 30px">
						<td style="width: 33%">'.$this->l('Average of SMS by message').'</td>
						<td style="width: 33%">'.$this->l('Paid by customers').'</td>
						<td style="width: 33%">'.$this->l('Orders with SMS notification').'</td>
					</tr>
					<tr>
						<td>' . round($average, 2) . '</td>
						<td>' . $paid . ' (' . ($total <= 0 ? '' : round($paid / $total * 100, 2)) . '%)</td>
						<td>' . $orders . ' (' . ($totalOrders <= 0 ? '' : round($orders / $totalOrders * 100, 2)) . '%)</td>
					</tr>
				</table>
			</fieldset>
		</form>';
	}

	private function _getTotal() {
		$res = Db::getInstance()->getRow("SELECT count(id_sendsms_logs) AS total FROM `" . _DB_PREFIX_ . "sendsms_logs` WHERE simulation != 1");
		return $res['total'];
	}

	private function _getTotalByState() {
		$result = array(0, 0);
		$res = Db::getInstance()->ExecuteS("SELECT distinct status, count(id_sendsms_logs) AS total FROM `" . _DB_PREFIX_ . "sendsms_logs` WHERE simulation != 1 GROUP BY status");
		foreach($res as $row) {
			$result[$row['status']] = $row['total'];
		}

		return $result;
	}

	private function _getTotalForAdmin() {
		$res = Db::getInstance()->getRow("SELECT count(id_sendsms_logs) AS total FROM `" . _DB_PREFIX_ . "sendsms_logs` WHERE id_customer IS NULL AND event != 'sendsmsFree' AND simulation != 1");
		return $res['total'];
	}

	private function _getTotalForCustomer() {
		$res = Db::getInstance()->getRow("SELECT count(id_sendsms_logs) AS total FROM `" . _DB_PREFIX_ . "sendsms_logs` WHERE id_customer > 0 AND simulation != 1");
		return $res['total'];
	}

	private function _getTotalFree() {
		$res = Db::getInstance()->getRow("SELECT count(id_sendsms_logs) AS total FROM `" . _DB_PREFIX_ . "sendsms_logs` WHERE event='sendsmsFree' AND simulation != 1");
		return $res['total'];
	}

	private function _getTotalPaidByCustomer() {
		$res = Db::getInstance()->getRow("SELECT count(id_sendsms_logs) AS total FROM `" . _DB_PREFIX_ . "sendsms_logs` WHERE paid_by_customer=1 AND simulation != 1");
		return $res['total'];
	}

	private function _getAverageCost() {
		$res = Db::getInstance()->getRow("SELECT AVG(nb_consumed) AS total FROM `" . _DB_PREFIX_ . "sendsms_logs` WHERE simulation != 1");
		return $res['total'];
	}

	public function _getOrdersWithSMS()
	{
		if (Configuration::get('SENDSMS_ID_PRODUCT')) {
			$res = Db::getInstance()->getRow('
				SELECT count(distinct(id_cart)) AS total
				FROM `'._DB_PREFIX_.'cart_product` AS cp
				JOIN `'._DB_PREFIX_.'cart` USING (id_cart)
				JOIN `'._DB_PREFIX_.'orders` USING (id_cart)
				WHERE cp.id_product = ' . Configuration::get('SENDSMS_ID_PRODUCT'));
			return $res['total'];
		} else {
			return 0;
		}
	}

	public function _getNbTotalOrders()
	{
		$res = Db::getInstance()->getRow('SELECT count(id_order) AS total FROM `'._DB_PREFIX_.'orders`');
		return $res['total'];
	}
}
?>