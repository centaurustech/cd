<?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

function getValues()
{
	// heure de fin de journée
	$hours = '20:00:00';

	// Nombre de commandes, total pour le jour, total pour le mois
	$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
	$query = '
		SELECT SUM(o.`total_paid_real`) as total_sales, COUNT(o.`total_paid_real`) as total_orders
		FROM `'._DB_PREFIX_.'orders` o
		WHERE (
			SELECT IF(os.`id_order_state` = 8, 0, 1)
			FROM `'._DB_PREFIX_.'orders` oo
			LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON oh.`id_order` = oo.`id_order`
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
			WHERE oo.`id_order` = o.`id_order`
			ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1
		) = 1 ';
	$result = Db::getInstance()->getRow($query.'
		AND o.`date_add` >= DATE_SUB(\''.date('Y-m-d').' '.$hours.'\', INTERVAL 1 DAY)
		AND o.`date_add` < \''.date('Y-m-d').' '.$hours.'\'');
	$result2 = Db::getInstance()->getRow($query.'AND o.`date_add` LIKE \''.date('Y-m').'-%\'');

	$values = array();
	$values['orders'] = intval($result['total_orders']);
	$values['day_sales'] = Tools::displayPrice($result['total_sales'], $currency, false);
	$values['month_sales'] = Tools::displayPrice($result2['total_sales'], $currency, false);

	// Nombre d'inscriptions
	$query = '
		SELECT COUNT(c.`id_customer`) as total_customers
		FROM `'._DB_PREFIX_.'customer` c
		WHERE c.`date_add` >= DATE_SUB(\''.date('Y-m-d').' '.$hours.'\', INTERVAL 1 DAY)
			AND c.`date_add` < \''.date('Y-m-d').' '.$hours.'\'';
	$result = Db::getInstance()->getRow($query);
	$values['subs'] = intval($result['total_customers']);

	// Nombre de visites
	$query = '
		SELECT COUNT(c.`id_connections`)
		FROM `'._DB_PREFIX_.'connections` c
		WHERE c.`date_add` >= DATE_SUB(\''.date('Y-m-d').' '.$hours.'\', INTERVAL 1 DAY)
			AND c.`date_add` < \''.date('Y-m-d').' '.$hours.'\'';
	$values['visits'] = Db::getInstance()->getValue($query);

	// Nombre de visiteurs
	$query = '
		SELECT COUNT(DISTINCT c.`id_guest`)
		FROM `'._DB_PREFIX_.'connections` c
		WHERE c.`date_add` >= DATE_SUB(\''.date('Y-m-d').' '.$hours.'\', INTERVAL 1 DAY)
			AND c.`date_add` < \''.date('Y-m-d').' '.$hours.'\'';
	$values['visitors'] = Db::getInstance()->getValue($query);

	return $values;
}


Module::hookExec('sendsmsDailyReport', getValues());
die('REPORT SENT');
?>