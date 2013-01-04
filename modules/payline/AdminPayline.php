<?php
/*
http://www.payline.com

Payline module for Prestashop 1.4.x - v1.2 - June 2012

Copyright (c) 2012 Monext
*/
require_once PS_ADMIN_DIR . '/../classes/AdminTab.php';

class AdminPayline extends AdminTab
{
	private $_module = 'payline';
  	private $_modulePath;
  	private $_html = '';
  	
  	public function __construct()
  	{
  		parent::__construct();
  	}
}

?>