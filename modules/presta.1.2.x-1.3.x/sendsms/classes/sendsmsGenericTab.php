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

	public function __construct()
	{
		global $cookie, $_LANGADM;

		$langFile = _PS_MODULE_DIR_.$this->_module.'/'.Language::getIsoById(intval($cookie->id_lang)).'.php';
		if(file_exists($langFile))
		{
			require_once $langFile;
			foreach($_MODULE as $key=>$value)
				if(strpos($key, get_class($this)) !== false)
					$_LANGADM[str_replace('_', '', strip_tags($key))] = $value;
		}
		parent::__construct();
	}
}
?>