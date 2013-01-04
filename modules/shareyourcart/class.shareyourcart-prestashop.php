<?php

/**
 * 	CLASS: Share Your Cart Wordpress Base
 * 	AUTHOR: Barandi Solutions
 * 	COUNTRY: Romania
 * 	EMAIL: catalin.paun@barandisolutions.ro
 * 	VERSION : 1.2
 * 	DESCRIPTION: This class is used as a base class for prestashop module.
 * *    Copyright (C) 2011 Barandi Solutions
 */
require_once(dirname(__FILE__) ."/sdk/class.shareyourcart-base.php");

if(!class_exists('ShareYourCartPrestashop',false)){ 

class ShareYourCartPrestashop extends ShareYourCartBase {

	protected static $_VERSION = 2;

    public $upload_directory_path = "upload";
    public $upload_directory_name = "shareyourcart";
    
	/**
	*
	* Get the plugin version.
	* @return an integer
	*
	*/
	protected function getPluginVersion(){
			
		return self::$_VERSION;
	}

    /**
     *
     * Return the shop's URL
     *
     */
    protected function getDomain() {
        $base_url = Tools::getShopDomain(true).__PS_BASE_URI__;
        if(substr($base_url, -1, 1) == "/")
                $base_url = substr($base_url, 0, strlen($base_url)-1);
        return $base_url;
    }
	
	/**
	*
	* Return the current module URL
	*
	*/
	protected function getModuleURL() {
		return $this->getDomain().'/modules/shareyourcart';
	}

    /**
     *
     * Return the email address of the Admin
     *
     */
    protected function getAdminEmail() {

        return Configuration::get('PS_SHOP_EMAIL');
    }

    /**
     *
     * Executes non queries
     *
     */
    public function executeNonQuery($sql) {
        if (!Db::getInstance()->Execute($sql))
            return false;

        return true;
    }

    /**
     *
     * Gets row(s) from the database
     *
     */
    public function getRow($sql) {
        if(substr($sql,0,4) == "show"){
          return Db::getInstance()->ExecuteS($sql);
        } else {
          return Db::getInstance()->getRow($sql);
        }
    }

    /**
     *
     * Inserts row(s) in DB
     *
     */
    public function insertRow($tableName, $data) {
		
		//make sure we protect the data from SQL injection
		foreach($data as $key => $value){
			
			if(is_string($value)){
				$data[$key] = pSQL($value);
			}
		}
		
		return (FALSE !== Db::getInstance()->autoExecuteWithNullValues($tableName, $data, 'INSERT'));
    }

    /**
     *
     * Gets the table name
     *
     */
    public function getTableName($key) {

        return _DB_PREFIX_ . $key;
    }

    /**
     *
     * Sets configuration value
     *
     */
    public function setConfigValue($field, $value) {
	
		//append shareyourcart, to avoid conflicts
		$field = "shareyourcart_" . $field;
		if(strlen($field) > 32) //prestashop can store only keys as long as 32, so convert it to a simpler version
			$field = md5($field);

        Configuration::updateValue($field, $value, true);
    }

    /**
     *
     * Gets configuration value
     *
     */
    public function getConfigValue($field) {

		//append shareyourcart, to avoid conflicts
		$field = "shareyourcart_" . $field;
		if(strlen($field) > 32) //prestashop can store only keys as long as 32, so convert it to a simpler version
			$field = md5($field);
			
        return Configuration::get($field);
    }

    /**
     *
     * Gets the secret key
     *
     */
    public function getSecretKey() {
        // Live KEY : 
        return 'efa4af9f-3bb7-441d-81cd-5b6bdabd34c1';
    }

	/**
	*
	* Return the jQuery sibling selector for the product button
	*
	*/
	protected function getProductButtonPosition(){
		$selector = parent::getProductButtonPosition();
		return (!empty($selector) ? $selector : "#buy_block .price");
	}
	
	/**
	*
	* Return the jQuery sibling selector for the cart button
	*
	*/
	protected function getCartButtonPosition(){
		$selector = parent::getCartButtonPosition();
		return (!empty($selector) ? $selector : "#cart_summary");
	}
	
	/**
     *
     * Gets the callback URL for the button
     *
     */
    public function getButtonCallbackURL() {
        
		$callback_url = $this->getModuleURL()."/callback.php?action=button";
        
		if($this->isSingleProduct()) {

			$callback_url .= '&p='.Tools::getValue('id_product');
		}

		return $callback_url;
	}
	
    /*
     *
     * Called when the button is pressed
     *
     */
    public function buttonCallback() {

		global $link, $cookie;
		
		$params = array(
			/* the URL which will be called when the coupon is created */
			'callback_url' =>   $this->getModuleURL()."/callback.php?action=coupon".(isset($_REQUEST['p']) ? '&p='.$_REQUEST['p'] : '' ),
			'success_url' => $this->getDomain().'/order.php',
			'cancel_url' => $this->getDomain().'/order.php',
		);

	//there is no product set, thus send the products from the shopping cart
	if(!isset($_REQUEST['p'])){
	
		//what is really important is that we save the cart id in session
		//for use when we get in the coupon callback, as there is no cookie there
		$_SESSION['syc_cart_id'] = $cookie->id_cart;
		$cart = new Cart((int)$cookie->id_cart); //load the cart from the session
		$products = $cart->getProducts();
		
		foreach($products as $product)
		{
			$params['cart'][] = array(
			"item_name" => $product['name'],
			"item_url" => $link->getProductLink($product['id_product']),
			"item_price" => Tools::displayPrice(Product::getPriceStatic($product['id_product'])),
			"item_description" => $product['description_short'],
			"item_picture_url" => $link->getImageLink($product['link_rewrite'],$product['id_image']),
			);
		}
	} else {
	
		$product = new Product((int)$_REQUEST['p'], true, $cookie->id_lang);
		
		$params['cart'][] = array(
			"item_name"        => $product->name,
			"item_url"         => $link->getProductLink($product),
			"item_price"       => Tools::displayPrice($product->getPrice(Product::$_taxCalculationMethod == PS_TAX_INC, false)),
			"item_description" => $product->description_short,
		);
		
		//add the main picture
		$images = $product->getImages((int)$cookie->id_lang);
		foreach($images as $image)
		{
			if ($image['cover'])
			{
				$params['cart'][0]["item_picture_url"] = $link->getImageLink($product->link_rewrite,(Configuration::get('PS_LEGACY_IMAGES') ? ($product->id.'-'.$image['id_image']) : $image['id_image']));
				break;
			}
		}
	}

    try {

			$this->startSession($params);

		} catch(Exception $e) {

		//display the error to the user
		echo $e->getMessage();
	}

	exit;
    }

    /**
     *
     * 	 Saves the coupon in to the database
     *
     */
    protected function saveCoupon($token, $coupon_code, $coupon_value, $coupon_type, $product_unique_ids) {

		global $cookie; //use a default cookie. don't rely on the fact that this is the same as the one the user has!
		
        // Insert Coupon into the DB
        switch ($coupon_type) {
            case 'amount': $discount_type = 2;
                break;
            case 'percent': $discount_type = 1;
                break;
            case 'free_shipping': $discount_type = 3;
                break;
            default : $discount_type = 1;
        }
		
		$coupon_data = array(
			'id_discount_type'       => $discount_type,
			'behavior_not_exhausted' => 1,
			'id_customer'            => 0,
			'id_group'               => 0,
			'id_currency'            => Currency::getCurrent()->id,
			'name'                   => $coupon_code,
			'value'                  => $coupon_value,
			'quantity'               => 1,
			'quantity_per_user'      => 1,
			'cumulable'              => 0,
			'cumulable_reduction'    => 0,
			'date_from'              => date('Y-m-d', strtotime('now')),
			'date_to'                => date('Y-m-d', strtotime('now +1 day')),
			'minimal'                => 0.00,
			'active'                 => 1,
			'cart_display'           => 0,
			'date_add'               => date('Y-m-d', strtotime('now')),
			'date_upd'               => date('Y-m-d', strtotime('now')),
		);

		if($this->insertRow($this->getTableName('discount'),$coupon_data) === FALSE)
		{
			//the save failed
			throw new Exception('Failed to save the coupon');
		}
		
		//get the coupon id
        $coupon_id = Db::getInstance()->Insert_ID();

        //get all available categories
        $results = Db::getInstance()->ExecuteS("SELECT id_category FROM ".$this->getTableName('category'));
		
		//TODO: we should assign the discount only to the product_unique_ids, 
		//but Prestashop does not support it, so for now
		// Assign the coupon to all categories
        foreach ($results as $res) {
	
			$this->insertRow($this->getTableName('discount_category'), array(
				'id_category' => $res['id_category'],
				'id_discount' => $coupon_id,
				));
        }

        // Inserts a coupon description
		$this->insertRow($this->getTableName('discount_lang'), array(
				'id_discount' => $coupon_id,
				'id_lang'     => $cookie->id_lang,
				'description' => 'Share Your Cart generated coupon',
		));

        //call the base class method
        parent::saveCoupon($token, $coupon_code, $coupon_value, $coupon_type, $product_unique_ids);
    }
	
	/**
     *
     * Applies the coupon automatically to the cart
     *
     */
    public function applyCoupon($coupon_code) {
        
		//DO NOT APPLY the coupon to the cart
		//as users will try to insert it manually, and it will give them an error:
		//You cannot use this voucher anymore (usage limit attained). 
		
		/*$cart = new Cart((int)$_SESSION['syc_cart_id']); //load the cart from the session
		//remove any already applied coupons
		Db::getInstance()->delete($this->getTableName('cart_discount'), "`id_cart` = $cart->id");
		
        $discount = new Discount((int)(Discount::getIdByName($coupon_code)));
		$cart->addDiscount((int)($discount->id));*/
    }

    /**
     *
     * Create url for the specified file. The file must be specified in relative path
     * to the base of the plugin
     *
     */
    protected function createUrl($file) {
      
        //get the real file path
        $file = realpath($file);

        //calculate the relative path from this file
        $file = SyC::relativepath(dirname(__FILE__), $file);
		
		//for prestashop the URL is simple
		return $this->getModuleUrl()."/$file";
    }

    /**
     *
     * Load session Data
     * No actual use for Prestashop
     *
     */
    public function loadSessionData() {

        return;
    }

    /**
     * isSingleProduct
     * @param null
     */
    public function isSingleProduct() {

		//if there is a product id in the URL, then we are on a single product page
		return (Tools::getValue('id_product') !== false);   //TODO: this will probably break if the shop uses some nice URLs, like wordpress permalinks
    }

    public function getUploadDir(){
       return _PS_ROOT_DIR_.DIRECTORY_SEPARATOR
              .$this->upload_directory_path
              .DIRECTORY_SEPARATOR.$this->upload_directory_name.DIRECTORY_SEPARATOR;
    }
    
    /**
	 * install the plugin
	 * @param null
	 * @return boolean
	 */
	public function install(&$message = null) {
      mkdir(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR
              .$this->upload_directory_path
              .DIRECTORY_SEPARATOR.$this->upload_directory_name, 0077);
      return parent::install($message);
    }
    
	/**
	 * uninstall the plugin
	 * @param null
	 * @return boolean
	 */
	public function uninstall(&$message = null) {
      $this->_removeDirectory(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR
              .$this->upload_directory_path
              .DIRECTORY_SEPARATOR.$this->upload_directory_name);
      return parent::uninstall($message);
    }
    
    private function _removeDirectory($dir){
      foreach(glob($dir . '/*') as $file) {
          if(is_dir($file))
              rrmdir($file);
          else
              unlink($file);
      }
      rmdir($dir);
    }
    
}
} //END IF
?>