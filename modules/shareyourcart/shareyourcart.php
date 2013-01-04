<?php

/*
 * 2011 Share Your Cart
 *
 *  @author Barandi Solutions <contact@barandisolutions.ro>
 *  @copyright  2011 Barandi Solutions
 *  @version  Release: $Revision: 123 $
 */

if (!defined('_CAN_LOAD_FILES_'))
    exit;
	
if (!defined('_PS_VERSION_'))
	exit;

//require_once (dirname(__FILE__).'/class.shareyourcart-prestashop.php');  //this does not work only in newer versions of Prestashop
require_once (_PS_ROOT_DIR_ . '/modules/shareyourcart/class.shareyourcart-prestashop.php');
class ShareYourCart extends Module{

    private $syc;

    /*
      Function: __construct
      Description: Constructor function
     */

    function __construct() {
        $this->syc = new ShareYourCartPrestashop();
        
        $this->name = "shareyourcart";
        $this->tab = 'social_networks';
        $this->author = "Barandi Solutions";
        $this->version = $this->syc->getVersion();
		$this->module_key = "e9bd392990f41c9e68c17d284f748d93";
		
        parent::__construct();

		//if the admin is in the Back Office, check for a newer version
		if(isset($_GET['tab'])) $this->syc->checkSDKStatus(); 
		
		$this->displayName = $this->l("ShareYourCart");
        $this->description = $this->l("<br />".
		$this->syc->getUpdateNotification(). //show an update notification ( if needed )
		"<br /><strong>Increase by 10%</strong> the number of Facebook shares and Twitter tweets that your customers do about your business.
<br /><br />
ShareYourCart enables owners to reward their customers for spreading the word about their products / services to their friends, by offering them a coupon code for their purchases, thus helping increase sales conversion
<br /><br />
You can choose how much of a discount to give (in fixed amount, percentage, or free shipping) and to which social media channels it should it be applied. You can also define what the advertisement should say, so that it fully benefits your sales."
	);

		//notify the admin there is a new version available ( if it is the case )		
		$this->warning = $this->l(trim(strip_tags($this->syc->getUpdateNotification()))); //. //first, show an update notification ( if needed )
		if(!empty($this->warning)) $this->warning .= $this->l('  '.$this->syc->getConfigValue("download_url"));  //if there was a notification, show the download url
    }

    /*
      Function: install
      Description: Function ran on module installation
     */

    public function install() {

        if(!parent::install())
			return false;
			
        // Creates the tables in the databse
        $this->syc->install();

        if (!$this->registerHook('productfooter'))
            return false;
        if (!$this->registerHook('shoppingCart'))
            return false;
        if (!$this->registerHook('header'))
            return false;

        return true;
        
    }
	
    /*
      Function: enable
      Description: Function ran on module activation
     */
	public function enable()
	{
		return parent::enable() && $this->syc->activate();
	}

	/*
      Function: disable
      Description: Function ran on module activation
     */
	public function disable()
	{
		return $this->syc->deactivate() && parent::disable();
	}	

    /*
      Function: uninstall
      Description: Function ran on module removal
     */
    public function uninstall() {

        $this->syc->uninstall();

        if (parent::uninstall() == false)
            return false;

        return true;
    }
	
	

    /*
      Function: hook HEAD
      Description: Hooks HEAD section
     */

    public function hookheader() {

        return $this->syc->getPageHeader();
    }

    /*
      Function: Product Footer Hook
      Description: Hooks
     */

    public function hookproductfooter($params) {
       
        // Sets the path to the module
        $this->syc->path = $this->_path;
		$this->syc->product = $params['product'];

        return $this->syc->getProductButton();
    }

    /*
      Function: Shopping Cart Hook 
      Description: Hooks
     */
    public function hookshoppingCart($params) {

        global $link;
        
        $this->syc->path = $this->_path;
        
        $this->syc->cart = $params['products'];
        $this->syc->cartlink = $link;
        
		return $this->syc->getCartButton().$this->_fetchjQueryMoveBlock();    
    }
    
    private function _fetchjQueryMoveBlock($distance_width = 15){
      $ret = "";//
      $ret .= '<script>';
      $ret .=   '$("#cart_voucher form fieldset:first").append("<div style=\";margin-top:'.(int)$distance_width.'px;\"></div>");';
      $ret .=   '$(".shareyourcart-button:first").appendTo("#cart_voucher form fieldset:first");';
      $ret .= '</script>';
      return $ret;
    }
    
    /*
      Function: getContent
      Description: Displays the admin page for the plugin with errors messages if any
     */

    public function getContent() {
      
       $html = $this->syc->getAdminHeader();
       $html .= $this->syc->getAdminPage("", true, false);
       $html .= $this->syc->getButtonCustomizationPage("", false, true);

       return $html;
    }

}

// End of class
?>