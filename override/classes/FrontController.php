<?PHP
/* Front Controller */

  //La classe d’origine située dans /classes/FrontController.php s’appelle FrontControllerCore, on utilise donc ici FrontController
class FrontController extends FrontControllerCore
{
  public function displayHeader()
  {
    global $css_files, $js_files;

    if (!self::$initialized)
      $this->init();

    // P3P Policies (http://www.w3.org/TR/2002/REC-P3P-20020416/#compact_policies)
    header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

    /* PATHs */
    self::$smarty->assign('blockuserinfo_path', dirname(__FILE__) . '/../../themes/cd/modules/blockuserinfo/blockuserinfo.tpl');
    self::$smarty->assign('blocklundisamedi_path', dirname(__FILE__) . '/../../themes/cd/lundi_au_samedi.tpl');
    self::$smarty->assign('blockbasket_path', dirname(__FILE__) . '/../../themes/cd/basket.tpl');
    self::$smarty->assign('menubar_path', dirname(__FILE__) . '/../../themes/cd/menu_bar.tpl');

    /* Hooks are volontary out the initialize array (need those variables already assigned) */
    self::$smarty->assign(array(
				'time' => time(),
				'img_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
				'static_token' => Tools::getToken(false),
				'token' => Tools::getToken(),
				'logo_image_width' => Configuration::get('SHOP_LOGO_WIDTH'),
				'logo_image_height' => Configuration::get('SHOP_LOGO_HEIGHT'),
				'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
				'content_only' => (int)Tools::getValue('content_only')
				));
    self::$smarty->assign(array(
				'HOOK_HEADER' => Module::hookExec('header'),
				'HOOK_TOP' => Module::hookExec('top'),
				'HOOK_LEFT_COLUMN' => Module::hookExec('leftColumn')
				));

    if ((Configuration::get('PS_CSS_THEME_CACHE') OR Configuration::get('PS_JS_THEME_CACHE')) AND is_writable(_PS_THEME_DIR_.'cache'))
      {
	// CSS compressor management
	if (Configuration::get('PS_CSS_THEME_CACHE'))
	  Tools::cccCss();

	//JS compressor management
	if (Configuration::get('PS_JS_THEME_CACHE'))
	  Tools::cccJs();
      }

    self::$smarty->assign('css_files', $css_files);
    self::$smarty->assign('js_files', array_unique($js_files));
    self::$smarty->display(_PS_THEME_DIR_.'header.tpl');
  }

	public function init()
	{
		global $useSSL, $cookie, $smarty, $cart, $iso, $defaultCountry, $protocol_link, $protocol_content, $link, $css_files, $js_files;

		if (self::$initialized)
			return;
		self::$initialized = true;

		// If current URL use SSL, set it true (used a lot for module redirect)
		if (Tools::usingSecureMode())
			$useSSL = $this->ssl = true;

		$css_files = array();
		$js_files = array();

		if ($this->ssl AND !Tools::usingSecureMode() AND Configuration::get('PS_SSL_ENABLED'))
		{
			header('HTTP/1.1 301 Moved Permanently');
			header('Cache-Control: no-cache');
			header('Location: '.Tools::getShopDomainSsl(true).$_SERVER['REQUEST_URI']);
			exit();
		}
		else if (Configuration::get('PS_SSL_ENABLED') AND Tools::usingSecureMode() AND !($this->ssl))
		{
			header('HTTP/1.1 301 Moved Permanently');
			header('Cache-Control: no-cache');
			header('Location: '.Tools::getShopDomain(true).$_SERVER['REQUEST_URI']);
			exit();
		}

		ob_start();

		/* Loading default country */
		$defaultCountry = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'), Configuration::get('PS_LANG_DEFAULT'));
		$cookieLifetime = (time() + (((int)Configuration::get('PS_COOKIE_LIFETIME_FO') > 0 ? (int)Configuration::get('PS_COOKIE_LIFETIME_FO') : 1)* 3600));
		$cookie = new Cookie('ps', '', $cookieLifetime);
		$link = new Link();

		if ($this->auth AND !$cookie->isLogged($this->guestAllowed))
			Tools::redirect('authentication.php'.($this->authRedirection ? '?back='.$this->authRedirection : ''));

		/* Theme is missing or maintenance */
		if (!is_dir(_PS_THEME_DIR_))
			die(Tools::displayError('Current theme unavailable. Please check your theme directory name and permissions.'));
		elseif (basename($_SERVER['PHP_SELF']) != 'disabled.php' AND !(int)(Configuration::get('PS_SHOP_ENABLE')))
			$this->maintenance = true;
		elseif (Configuration::get('PS_GEOLOCATION_ENABLED'))
			$this->geolocationManagement();

		// Switch language if needed and init cookie language
		if ($iso = Tools::getValue('isolang') AND Validate::isLanguageIsoCode($iso) AND ($id_lang = (int)(Language::getIdByIso($iso))))
			$_GET['id_lang'] = $id_lang;

		Tools::switchLanguage();
		Tools::setCookieLanguage();

		/* attribute id_lang is often needed, so we create a constant for performance reasons */
		if (!defined('_USER_ID_LANG_'))
			define('_USER_ID_LANG_', (int)$cookie->id_lang);

		if (isset($_GET['logout']) OR ($cookie->logged AND Customer::isBanned((int)$cookie->id_customer)))
		{
			$cookie->logout();
			Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL);
		}
		elseif (isset($_GET['mylogout']))
		{
			$cookie->mylogout();
			Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL);
		}

		global $currency;
		$currency = Tools::setCurrency();

		/* Cart already exists */
		if ((int)$cookie->id_cart)
		{
			$cart = new Cart((int)$cookie->id_cart);
			if ($cart->OrderExists())
				unset($cookie->id_cart, $cart, $cookie->checkedTOS);
			/* Delete product of cart, if user can't make an order from his country */
			elseif (intval(Configuration::get('PS_GEOLOCATION_ENABLED')) AND
					!in_array(strtoupper($cookie->iso_code_country), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES'))) AND
					$cart->nbProducts() AND intval(Configuration::get('PS_GEOLOCATION_NA_BEHAVIOR')) != -1 AND
					!self::isInWhitelistForGeolocation())
				unset($cookie->id_cart, $cart);
			elseif ($cookie->id_customer != $cart->id_customer OR $cookie->id_lang != $cart->id_lang OR $cookie->id_currency != $cart->id_currency)
			{
				if ($cookie->id_customer)
					$cart->id_customer = (int)($cookie->id_customer);
				$cart->id_lang = (int)($cookie->id_lang);
				$cart->id_currency = (int)($cookie->id_currency);
				$cart->update();
			}
			/* Select an address if not set */
			if (isset($cart) && (!isset($cart->id_address_delivery) || $cart->id_address_delivery == 0 ||
				!isset($cart->id_address_invoice) || $cart->id_address_invoice == 0) && $cookie->id_customer)
			{
				$to_update = false;
				if (!isset($cart->id_address_delivery) || $cart->id_address_delivery == 0)
				{
					$to_update = true;
					$cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
				}
				if (!isset($cart->id_address_invoice) || $cart->id_address_invoice == 0)
				{
					$to_update = true;
					$cart->id_address_invoice = (int)Address::getFirstCustomerAddressId($cart->id_customer);
				}
				if ($to_update)
					$cart->update();
			}
		}

		if (!isset($cart) OR !$cart->id)
		{
			$cart = new Cart();
			$cart->id_lang = (int)($cookie->id_lang);
			$cart->id_currency = (int)($cookie->id_currency);
			$cart->id_guest = (int)($cookie->id_guest);
			if ($cookie->id_customer)
			{
				$cart->id_customer = (int)($cookie->id_customer);
				$cart->id_address_delivery = (int)(Address::getFirstCustomerAddressId($cart->id_customer));
				$cart->id_address_invoice = $cart->id_address_delivery;
			}
			else
			{
				$cart->id_address_delivery = 0;
				$cart->id_address_invoice = 0;
			}
		}
		if (!$cart->nbProducts())
			$cart->id_carrier = NULL;

		$locale = strtolower(Configuration::get('PS_LOCALE_LANGUAGE')).'_'.strtoupper(Configuration::get('PS_LOCALE_COUNTRY').'.UTF-8');
		setlocale(LC_COLLATE, $locale);
		setlocale(LC_CTYPE, $locale);
		setlocale(LC_TIME, $locale);
		setlocale(LC_NUMERIC, 'en_US.UTF-8');

		if (Validate::isLoadedObject($currency))
			$smarty->ps_currency = $currency;
		if (Validate::isLoadedObject($ps_language = new Language((int)$cookie->id_lang)))
			$smarty->ps_language = $ps_language;

		/* get page name to display it in body id */
		$page_name = (isset($this->php_self) ? preg_replace('/\.php$/', '', $this->php_self) : '');
		if (preg_match('#^'.__PS_BASE_URI__.'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m))
			$page_name = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);

		$smarty->assign(Tools::getMetaTags($cookie->id_lang, $page_name));
		$smarty->assign('request_uri', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));

		/* Breadcrumb */
		$navigationPipe = (Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>');
		$smarty->assign('navigationPipe', $navigationPipe);

		$protocol_link = (Configuration::get('PS_SSL_ENABLED') OR Tools::usingSecureMode()) ? 'https://' : 'http://';

		$useSSL = ((isset($this->ssl) AND $this->ssl AND Configuration::get('PS_SSL_ENABLED')) OR Tools::usingSecureMode()) ? true : false;
		$protocol_content = ($useSSL) ? 'https://' : 'http://';
		if (!defined('_PS_BASE_URL_'))
			define('_PS_BASE_URL_', Tools::getShopDomain(true));
		if (!defined('_PS_BASE_URL_SSL_'))
			define('_PS_BASE_URL_SSL_', Tools::getShopDomainSsl(true));

		$link->preloadPageLinks();
		$this->canonicalRedirection();

		Product::initPricesComputation();

		$display_tax_label = $defaultCountry->display_tax_label;
		if ($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
		{
			$infos = Address::getCountryAndState((int)($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
			$country = new Country((int)$infos['id_country']);
			if (Validate::isLoadedObject($country))
				$display_tax_label = $country->display_tax_label;
		}

		$smarty->assign(array(
			'link' => $link,
			'cart' => $cart,
			'currency' => $currency,
			'cookie' => $cookie,
			'page_name' => $page_name,
			'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
			'base_dir_ssl' => $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__,
			'content_dir' => $protocol_content.Tools::getHttpHost().__PS_BASE_URI__,
			'tpl_dir' => _PS_THEME_DIR_,
			'modules_dir' => _MODULE_DIR_,
			'mail_dir' => _MAIL_DIR_,
			'lang_iso' => $ps_language->iso_code,
			'come_from' => Tools::getHttpHost(true, true).Tools::htmlentitiesUTF8(str_replace('\'', '', urldecode($_SERVER['REQUEST_URI']))),
			'cart_qties' => (int)$cart->nbProducts(),
			'currencies' => Currency::getCurrencies(),
			'languages' => Language::getLanguages(),
			'priceDisplay' => Product::getTaxCalculationMethod(),
			'add_prod_display' => (int)Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'shop_name' => Configuration::get('PS_SHOP_NAME'),
			'roundMode' => (int)Configuration::get('PS_PRICE_ROUND_MODE'),
			'use_taxes' => (int)Configuration::get('PS_TAX'),
			'display_tax_label' => (bool)$display_tax_label,
			'vat_management' => (int)Configuration::get('VATNUMBER_MANAGEMENT'),
			'opc' => (bool)Configuration::get('PS_ORDER_PROCESS_TYPE'),
			'PS_CATALOG_MODE' => (bool)Configuration::get('PS_CATALOG_MODE'),
		));

		// Deprecated
		$smarty->assign(array(
			'id_currency_cookie' => (int)$currency->id,
			'logged' => $cookie->isLogged(),
			'customerName' => ($cookie->logged ? $cookie->customer_firstname.' '.$cookie->customer_lastname : false)
		));

		// TODO for better performances (cache usage), remove these assign and use a smarty function to get the right media server in relation to the full ressource name
		$assignArray = array(
			'img_ps_dir' => _PS_IMG_,
			'img_cat_dir' => _THEME_CAT_DIR_,
			'img_lang_dir' => _THEME_LANG_DIR_,
			'img_prod_dir' => _THEME_PROD_DIR_,
			'img_manu_dir' => _THEME_MANU_DIR_,
			'img_sup_dir' => _THEME_SUP_DIR_,
			'img_ship_dir' => _THEME_SHIP_DIR_,
			'img_store_dir' => _THEME_STORE_DIR_,
			'img_col_dir' => _THEME_COL_DIR_,
			'img_dir' => _THEME_IMG_DIR_,
			'css_dir' => _THEME_CSS_DIR_,
			'js_dir' => _THEME_JS_DIR_,
			'pic_dir' => _THEME_PROD_PIC_DIR_
		);

		foreach ($assignArray as $assignKey => $assignValue)
			if (substr($assignValue, 0, 1) == '/' OR $protocol_content == 'https://')
				$smarty->assign($assignKey, $protocol_content.Tools::getMediaServer($assignValue).$assignValue);
			else
				$smarty->assign($assignKey, $assignValue);

		// setting properties from global var
		self::$cookie = $cookie;
		self::$cart = $cart;
		self::$smarty = $smarty;
		self::$link = $link;

		if ($this->maintenance)
			$this->displayMaintenancePage();
		if ($this->restrictedCountry)
			$this->displayRestrictedCountryPage();

		//live edit
		if (Tools::isSubmit('live_edit') AND $ad = Tools::getValue('ad') AND (Tools::getValue('liveToken') == sha1(Tools::getValue('ad')._COOKIE_KEY_)))
			if (!is_dir(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.$ad))
				die(Tools::displayError());


		$this->iso = $iso;
		$this->setMedia();

		/* ** New code for wordpress meta tags ** */
		/*********************************
		 WordPress blog gestion des métas
		**********************************/
		$id_wp = '';
		$url = $_SERVER['REQUEST_URI'];
		$parts = explode('/',$url);
		foreach($parts as $part){
		  if(strpos($part,'wpid')!==false){
		    $id_wp = str_replace('wpid','',$part);
		  }
		}
		// détection WP
		/* if(!empty($_GET['p']) || !empty($id_wp)){ */
		/*   // on prend la valeur en $_GET si l'url-rew. désactivé */
		/*   if(!empty($_GET['p'])){$id_wp=$_GET['p'];} */
		/*   // meta title */
		/*   $title = get_the_title($id_wp); */
		/*   $smarty->assign('meta_title',$title); */
		/*   // meta desc */
		/*   $smarty->assign('meta_description',$title); */
		/*   // meta keywords */
		/*   $tags = get_the_tags($id_wp); */
		/*   $meta_keywords = ''; */
		/*   foreach($tags as $tag){$meta_keywords.=$tag->name.',';} */
		/*   $meta_keywords = substr($meta_keywords,0,-1); */
		/*   $smarty->assign('meta_keywords',$meta_keywords); */
		/* } */
		/**********************************/
	}
}

?>