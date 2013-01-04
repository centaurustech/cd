<?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');

class sendsmsGenericTab extends AdminTab
{
	protected $_module = 'sendsms';
	protected $_html = '';
}
?>