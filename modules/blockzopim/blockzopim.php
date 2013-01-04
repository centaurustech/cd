<?php

class blockzopim extends Module {
	
	public function __construct() {
		$this->name = 'blockzopim';
		$this->tab = version_compare(_PS_VERSION_, '1.4.0.0', '>=')?'advertising_marketing':'Mediacom87';
		$this->version = '1.4';
		$this->need_instance = 0;
		$this->author = 'Mediacom87';
		parent::__construct();
		$this->displayName = $this->l('Block Zopim');
		$this->description = $this->l('Integrate your Zopim script on your site.');
	}
	
	function install()
	{
		if (!parent::install() 
			OR !$this->registerHook('footer')
			OR !Configuration::updateValue('ZOPIM', ''))
			return false;
		return true;
	}
	
	function uninstall()
	{
		if (!Configuration::deleteByName('ZOPIM') OR !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$output = '<h2><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitblockzopim'))
		{
			$zopimscript = Tools::getValue('zopimscript');
			
			Configuration::updateValue('ZOPIM', $zopimscript);
			$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		global $cookie;
		
		$iso = Language::getIsoById((int)$cookie->id_lang);
		return '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<p>'.$this->l('Above all things, subscribe to').' <a href="https://www.zopim.com/affiliates/landing/mediacom87" title="'.$this->l('Above all things, subscribe to').' ZOPIM" target="_blank" style="color:orange"><b>ZOPIM</b></a></p>
			<fieldset>
				<legend><a href="http://www.prestatoolbox.'.(($iso != 'fr')?'com':'fr').'/?utm_source=module&utm_medium=cpc&utm_campaign=zopim"><img src="'.$this->_path.'logo.gif" alt="" /></a>'.$this->l('Settings').'</legend>
				
				<label><a href="https://www.zopim.com/affiliates/landing/mediacom87" target="_blank" title="'.$this->l('Zopim live chat solutions Create your account').'"><img src="http://netdna.prestatoolbox.net/images/zopim-logo-135x48.gif" alt="'.$this->l('Zopim live chat solutions').'" /></a></label>
				<div class="margin-form"><input type="text" name="zopimscript" value="'.Configuration::get('ZOPIM').'" />
					<p>'.$this->l('To configure this module, after registering on').' <a href="https://www.zopim.com/affiliates/landing/mediacom87" title="'.$this->l('Above all things, subscribe to').' ZOPIM" target="_blank" style="color:orange"><b>ZOPIM</b></a>, '.$this->l('get code to insert the script and find the ID of your site in bold red represent the example below. Enter the ID above.').'</p>
					<p class="clear">&lt;!-- Start of Zopim Live Chat Script --&gt;<br />
					&lt;script type=&quot;text/javascript&quot;&gt;<br />
					document.write(unescape(&quot;%3Cscript src=\'&quot; + document.location.protocol + &quot;//zopim.com/?<b><span style="color:#900" title="'.$this->l('Copy your site ID represented like this one').'">Ls3s2zqg6mTq4laFyAfVeLkTVnK5YPHn</span></b>\' charset=\'utf-8\' type=\'text/javascript\'%3E%3C/script%3E&quot;));<br />
					&lt;/script&gt;<br />
					&lt;!-- End of Zopim Live Chat Script --&gt;</p>
				</div>
				
				<center><input type="submit" name="submitblockzopim" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>
		
		<fieldset class="space">
				<legend><a href="https://www.paypal.com/fr/mrb/pal=LG5H4P9K8K6FC" target="_blank"><img src="http://netdna.prestatoolbox.net/images/paypal-icon-16x16.png" alt="" /></a>'.$this->l('Donation').'</legend>
				<p><a href="http://www.prestatoolbox.'.(($iso != 'fr')?'com':'fr').'/?utm_source=module&utm_medium=cpc&utm_campaign=zopim" target="_blank" title="'.$this->l('Mediacom87 WebAgency').'">'.$this->l('This module was developed and generously offered to the PrestaShop\'s community by Mediacom87 WebAgency specializing in supporting eCommerce.').'</a></p>
				<p><a href="http://www.prestatoolbox.'.(($iso != 'fr')?'com':'fr').'/?utm_source=module&utm_medium=cpc&utm_campaign=zopim" target="_blank" title="'.$this->l('Mediacom87 WebAgency').'">'.$this->l('If you want to support the Mediacom87\'s process, you can do so by making a donation.').'</a></p>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" style="text-align:center">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="MDQZ82DZ8UEQQ">
				<input type="image" src="https://www.paypal.com/fr_FR/FR/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
				<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
				</form>


		</fieldset>
		
		<fieldset class="space">
				<legend><img src="http://netdna.prestatoolbox.net/images/google-icon-16x16.png" alt="" height="16" width="16" /> '.$this->l('Ads').'</legend>
				<p><a href="http://www.prestatoolbox.'.(($iso != 'fr')?'com':'fr').'/?utm_source=module&utm_medium=cpc&utm_campaign=skysabar" target="_blank" title="'.$this->l('Mediacom87 WebAgency').'">'.$this->l('You can also support our agency by clicking the advertising below').'.</a></p>
				<p style="text-align:center"><script type="text/javascript"><!--
				google_ad_client = "ca-pub-1663608442612102";
				/* Zopim 728x90 */
				google_ad_slot = "5753334670";
				google_ad_width = 728;
				google_ad_height = 90;
				//-->
				</script>
				<script type="text/javascript"
				src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
				</script></p>
		</fieldset>
		';
	}

	function hookFooter($params)
	{
		global $smarty;
		$zopim = Configuration::get('ZOPIM');
		if ($zopim) {
			$smarty->assign(array('zopim' => $zopim));
			return $this->display(__FILE__, 'blockzopim.tpl');
		}
		return $output;
	}
}

?>
