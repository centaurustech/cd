<?php
/**
 * Cron module
 * Cron is a time-based job scheduler in Unix-like computer operating systems.
 * This module automaticaly executes jobs like Cron
 *
 * Add a cron job
 * Module::getInstanceByName('cron')->addCron($this->id, 'myMethod', '5 * * * *');
 *
 * last parameter details :
 * .---------------- minute (0 - 59) 
 * |  .------------- hour (0 - 23)
 * |  |  .---------- day of month (1 - 31)
 * |  |  |  .------- month (1 - 12) 
 * |  |  |  |  .---- day of week (0 - 6) (Sunday=0 )
 * |  |  |  |  |
 * *  *  *  *  *
 *
 * remarks :
 * It accepts the standard crontab format except steps ('/') :  0-50/5 * * * * isn't valid
 * 
 * exemples :
 * - 1 0 * * * : 00:01 of every day of the month, of every day of the week
 * - 15 3 * * 1-5 : every weekday morning at 3:15 am
 * - 0 0 1,15-17 * * : the first, fifteenth, sixteenth and seventeenth of each month at 00:00
 * - 0 0 * * 1 : every Monday at 00:00
 * 
 * Delete a job
 * Module::getInstanceByName('cron')->deleteCron($this->id, 'myMethod');
 * 
 * @category Prestashop
 * @category Module
 * @author Samdha <contact@samdha.net>
 * @copyright Samdha
 * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
 * @author logo Alessandro Rei http://www.kde-look.org/content/show.php/Dark-Glass+reviewed?content=67902
 * @license logo http://www.gnu.org/copyleft/gpl.html GPLv3
 * @version 1.3
**/
if (!class_exists('cron', false)){
	
class cron extends Module
{
	private $_postErrors = array();
	const INSTALL_SQL_FILE = 'install.sql';

	public function __construct()
	{
		$this->name = 'cron';
	 	$this->tab = version_compare(_PS_VERSION_, '1.4.0.0', '<')?'Tools':'administration';
		$this->version = '1.3';
		$this->author = 'Samdha';
		$this->need_instance = false;

		parent::__construct();

		$this->displayName = $this->l('Crontab for Prestashop');
		$this->description = $this->l('Allows modules to schedule jobs to run automatically at a certain time or date.');
	}
	
	/**
	 * install the module
	 *
	 * create table in BDD
	 * hook the module to footer
	**/
	public function install()
	{
		require_once(dirname(__FILE__).'/CronParser.php');
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return (false);
		else if (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return (false);
		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($sql AS $query)
			if($query)
				if(!Db::getInstance()->Execute(trim($query)))
					return false;
				
	 	if (!parent::install()
			OR !$this->registerHook('footer')
			OR !Configuration::updateValue('cron_method', 'traffic')
			)
	 		return false;
		return true;
	}
	
	public function uninstall()
	{
		return (Configuration::deleteByName('cron_lasttime') AND
				Configuration::deleteByName('cron_method') AND
				Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'cron') AND
				Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'cron_url') AND
				parent::uninstall());
	}

	public function getContent($tab = 'AdminModules') {
		global $currentIndex, $cookie;
		$token = Tools::getAdminToken($tab.intval(Tab::getIdFromClassName($tab)).intval($cookie->id_employee));

		$this->_postProcess($token);
			
        $output = '<h2>'.$this->displayName.'</h2>';
		$output .= $this->_displayErrors();
        $output .= $this->_displayForm($token);
        $output .= $this->_displayList($token);

		return $output;
    }

	/**
	 * display errors list
	 * 
	 * @var array self::$_postErrors
	 * @return string error HTML
	 */
	private function _displayErrors() {
		$nbErrors = sizeof($this->_postErrors);
		$output = '';
		if ($nbErrors) {
			if (method_exists($this, 'displayError'))
				foreach ($this->_postErrors as $error)
					$output .= $this->displayError($error);
			else {
				$output .= '
					<div class="alert error">
						<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
						<ol>';
					foreach ($this->_postErrors AS $error)
						$output .= '<li>'.$error.'</li>';
					$output .= '
						</ol>
					</div>';
			}
		}
		return $output;
	}
	
	/**
	 * display admin form
	 * 
	 * @param string $token
	 * @return string The from
	 */
    private function _displayForm($token) {
		global $currentIndex, $cookie;
		$cron_test = $this->cronExists($this->id, 'test');
		$cron_method = Configuration::get('cron_method');
		
		$output = '';
		if ($cron_test) {
			if ($cron_lasttest = Configuration::get('cron_lasttest'))
				$output .= '
					<div class="conf confirm" style="width:898px">
						<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
						'.$this->l('Last test have been successfully executed on').' '.Tools::DisplayDate(date('Y-m-d H:i:s', $cron_lasttest), $cookie->id_lang, true).' 
					</div>';
			else
				$output .= '
					<div class="alert">
						'.$this->l('Test have not been executed yet.').' 
					</div>';
		}
		
		$output .= '
			<fieldset style="float: right; width: 255px">
				<legend>'.$this->l('About').'</legend>
				<p style="font-size: 1.5em; font-weight: bold; padding-bottom: 0"><img src="'.$this->_path.'logo.png" alt="'.$this->displayName.'" style="float: left; padding-right: 1em"/>'.$this->displayName.'</p>
				<p style="clear: both">
				'.$this->description.'
				</p>
				<p>
				'.$this->l('Developped with love by').' <a style="color: #7ba45b; text-decoration: underline;" href="http://www.samdha.net">Samdha</a>'.$this->l(', which helps you develop your e-commerce site.').'
				</p>
				<p>
				<a href="http://www.samdha.net/contactez-nous"><img src="../img/admin/email.gif" alt="" /> '.$this->l('Contact').'</a>
				</p>
			</fieldset>
				
	        <form action="'.$currentIndex.'&amp;configure='.$this->name.'&amp;token='.$token.'" method="post">
				<fieldset class="width3">
					<legend>'.$this->l('Parameters').'</legend>
					
					<p>
					'.$this->l('Thanks for installing the cron module on your website.').'
					</p>
					<p>
					'.$this->l('Please choose the method used to determine when executing jobs.').'
					</p>
					
					<label for="cron_method">'.$this->l('Method').'</label>
					<div class="margin-form">
						<select name="cron_method" id="cron_method">
							<option value="traffic" '.($cron_method == 'traffic'?'selected="selected"':'').'>'.$this->l('Shop traffic').'</option>
							<option value="crontab" '.($cron_method == 'crontab'?'selected="selected"':'').'>'.$this->l('Server crontab').'</option>
							<option value="webcron" '.($cron_method == 'webcron'?'selected="selected"':'').'>'.$this->l('Webcron service').'</option>
						</select>
					</div>
					
					<hr/>
					<p>
					'.$this->l('"Shop traffic" method doesn\'t need configuration but is not sure. It depends of your website frequentation so when it isn\'t visited, jobs are not executed.').'
					</p>
					<hr/>
					<p>
					'.$this->l('"Server crontab" is the best method but only if your server uses Linux and you have access to crontab. In that case add the line below to your crontab file.').'
					</p>
					<code>* * * * * php -f '.dirname(__FILE__).DIRECTORY_SEPARATOR.'cron_crontab.php</code>
					<hr/>
					<p>
					'.$this->l('"Webcron service" is a good alternative to crontab but is often not free. Register to a service like').' <a href="http://www.webcron.org">webcron.org</a> 
					'.$this->l('and configure it to visit the URL below every minutes or the nearest.').'
					</p>
					<code>http://'.$this->getHttpHost(false, true).$this->_path.'cron_webcron.php</code>

					<hr/>
					<p>
					'.$this->l('To check whether the choosen method works, you can enable the test job. It should be executed every minutes and show it at the top of this form. When everything is ok you can disable it.').'
					</p>
					<label for="cron_test">'.$this->l('Test job').'</label>
					<div class="margin-form">
						<select name="cron_test" id="cron_test">
							<option value="1" '.($cron_test?'selected="selected"':'').'>'.$this->l('Enable').'</option>
							<option value="0" '.(!$cron_test?'selected="selected"':'').'>'.$this->l('Disable').'</option>
						</select>
					</div>
				</fieldset>
				<p><input type="submit" class="button" name="saveSettings" value="'.$this->l('Save').'" /></p>
			</form>

	        <form action="'.$currentIndex.'&amp;configure='.$this->name.'&amp;token='.$token.'" method="post" id="cron_add">
				<fieldset class="space width3">
					<legend>'.$this->l('Add a job').'</legend>
					<p>
					'.$this->l('The URL below will be visited at the time or date specified.').'
					</p>
					
					<label for="cron_method">'.$this->l('URL').'</label>
					<div class="margin-form">
						<input type="text" name="cron_url" id="cron_url" value="'.(isset($_POST['cron_url'])?$_POST['cron_url']:'').'" />
					</div>
					
					<label for="cron_method">'.$this->l('Schedule').'</label>
					<div class="margin-form">
						<input type="text" name="cron_mhdmd" id="cron_mhdmd" value="'.(isset($_POST['cron_mhdmd'])?$_POST['cron_mhdmd']:'').'" />
						<select style="display: none;" name="cron_mhdmd2" id="cron_mhdmd2">
							<option value="0 * * * *">'.$this->l('Every hours').'</option>
							<option value="0 0 * * *" selected="selected">'.$this->l('Daily (midnight)').'</option>
							<option value="0 0 * * 0">'.$this->l('Weekly (Sunday)').'</option>
							<option value="0 0 1 * *">'.$this->l('Monthly (first)').'</option>
							<option value="0 0 1 1 *" >'.$this->l('Yearly (January 1)').'</option>
							<option value="other" >'.$this->l('Other').'</option>
						</select>
					</div>
					
					<script type="text/javascript"><!--//
						$(document).ready(function() {
							$("#cron_mhdmd").hide();
							$("#cron_mhdmd2").show();
							$("#cron_mhdmd2").click(function() {
								if ($(this).val() == "other") {
									$("#cron_table").show();
									$("#cron_add fieldset").css("width", "900px");
								}
								else {
									$("#cron_table").hide();
									$("#cron_add fieldset").css("width", "");
								}
							});
							$("#cron_add").submit(function() {
								// http://www.php.net/manual/fr/function.preg-match.php#93824
								var reg = /^https?\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?([a-z0-9-.]*)(\.([a-z]{2,3}))?(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?$/i;
							    if ( !reg.test($("#cron_url").val() ) ) {
									alert (\''.$this->l('Invalide URL', __CLASS__, true).'\');
									return false;
							    }
								
								var mhdmd = "";
								if ($("#cron_mhdmd2").val() != "other")
									mhdmd = $("#cron_mhdmd2").val();
								else {
									// minutes
									if ($("input:radio[name=all_mins]:checked").val() == "1")
										mhdmd = "*";
									else {
										var tmp = "";
										$("select[name=mins]").each(function(){
											if ($(this).val())
												tmp = tmp + "," + $(this).val().join(",");											
										});
										if (tmp == "")
											tmp = ",*";
										mhdmd = tmp.slice(1);
									}

									// hours
									mhdmd = mhdmd + " ";
									if ($("input:radio[name=all_hours]:checked").val() == "1")
										mhdmd = mhdmd + "*";
									else {
										var tmp = "";
										$("select[name=hours]").each(function(){
											if ($(this).val())
												tmp = tmp + "," + $(this).val().join(",");											
										});
										if (tmp == "")
											tmp = ",*";
										mhdmd = mhdmd + tmp.slice(1);
									}

									// days
									mhdmd = mhdmd + " ";
									if ($("input:radio[name=all_days]:checked").val() == "1")
										mhdmd = mhdmd + "*";
									else {
										var tmp = "";
										$("select[name=days]").each(function(){
											if ($(this).val())
												tmp = tmp + "," + $(this).val().join(",");											
										});
										if (tmp == "")
											tmp = ",*";
										mhdmd = mhdmd + tmp.slice(1);
									}

									// months
									mhdmd = mhdmd + " ";
									if ($("input:radio[name=all_months]:checked").val() == "1")
										mhdmd = mhdmd + "*";
									else {
										var tmp = "";
										$("select[name=months]").each(function(){
											if ($(this).val())
												tmp = tmp + "," + $(this).val().join(",");											
										});
										if (tmp == "")
											tmp = ",*";
										mhdmd = mhdmd + tmp.slice(1);
									}
									
									// weekdays
									mhdmd = mhdmd + " ";
									if ($("input:radio[name=all_weekdays]:checked").val() == "1")
										mhdmd = mhdmd + "*";
									else {
										var tmp = "";
										$("select[name=weekdays]").each(function(){
											if ($(this).val())
												tmp = tmp + "," + $(this).val().join(",");											
										});
										if (tmp == "")
											tmp = ",*";
										mhdmd = mhdmd + tmp.slice(1);
									}
									
								}

								$("#cron_mhdmd").val(mhdmd);
							});
						});
						
						function enable_cron_fields(name, form, ena)
						{
							var els = form.elements[name];
							els.disabled = !ena;
							for(i=0; i<els.length; i++) {
							  els[i].disabled = !ena;
							  }
							}
					//--></script>
					<table class="table" style="display: none; width:100%" id="cron_table">
						<thead>
							<tr>
								<th>'.$this->l('Minutes').'</th>
								<th>'.$this->l('Hours').'</th>
								<th>'.$this->l('Days of month').'</th>
								<th>'.$this->l('Months').'</th>
								<th>'.$this->l('Days of week').'</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="5">'.$this->l('Use [ctrl] or [command] to select more than one values').'</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td valign="top">
									<input type="radio" name="all_mins" value="1" checked="checked" onclick="enable_cron_fields(\'mins\', form, 0)"/> '.$this->l('All').'<br/>
									<input type="radio" name="all_mins" value="0" onclick="enable_cron_fields(\'mins\', form, 1)"/> '.$this->l('Selected').'<br/>
									<table>
										<tr>
											<td valign="top">
												<select multiple="multiple" size="12" name="mins" disabled="disabled">
													<option value="0" >00</option>
													<option value="1" >01</option>
													<option value="2" >02</option>
													<option value="3" >03</option>
													<option value="4" >04</option>
													<option value="5" >05</option>
													<option value="6" >06</option>
													<option value="7" >07</option>
													<option value="8" >08</option>
													<option value="9" >09</option>
													<option value="10" >10</option>
													<option value="11" >11</option>
												</select>
											</td>
											<td valign="top">
												<select multiple="multiple" size="12" name="mins" disabled="disabled">
													<option value="12" >12</option>
													<option value="13" >13</option>
													<option value="14" >14</option>
													<option value="15" >15</option>
													<option value="16" >16</option>
													<option value="17" >17</option>
													<option value="18" >18</option>
													<option value="19" >19</option>
													<option value="20" >20</option>
													<option value="21" >21</option>
													<option value="22" >22</option>
													<option value="23" >23</option>
												</select>
											</td>
											<td valign="top">
												<select multiple="multiple" size="12" name="mins" disabled="disabled">
													<option value="24" >24</option>
													<option value="25" >25</option>
													<option value="26" >26</option>
													<option value="27" >27</option>
													<option value="28" >28</option>
													<option value="29" >29</option>
													<option value="30" >30</option>
													<option value="31" >31</option>
													<option value="32" >32</option>
													<option value="33" >33</option>
													<option value="34" >34</option>
													<option value="35" >35</option>
												</select>
											</td>
											<td valign="top">
												<select multiple="multiple" size="12" name="mins" disabled="disabled">
													<option value="36" >36</option>
													<option value="37" >37</option>
													<option value="38" >38</option>
													<option value="39" >39</option>
													<option value="40" >40</option>
													<option value="41" >41</option>
													<option value="42" >42</option>
													<option value="43" >43</option>
													<option value="44" >44</option>
													<option value="45" >45</option>
													<option value="46" >46</option>
													<option value="47" >47</option>
												</select>
											</td>
											<td valign="top">
												<select multiple="multiple" size="12" name="mins" disabled="disabled">
													<option value="48" >48</option>
													<option value="49" >49</option>
													<option value="50" >50</option>
													<option value="51" >51</option>
													<option value="52" >52</option>
													<option value="53" >53</option>
													<option value="54" >54</option>
													<option value="55" >55</option>
													<option value="56" >56</option>
													<option value="57" >57</option>
													<option value="58" >58</option>
													<option value="59" >59</option>
												</select>
											</td>
										</tr>
									</table>
								</td>
								<td valign="top">
									<input type="radio" name="all_hours" value="1"  checked="checked" onclick="enable_cron_fields(\'hours\', form, 0)"/> '.$this->l('All').'<br/>
									<input type="radio" name="all_hours" value="0"  onclick="enable_cron_fields(\'hours\', form, 1)"/> '.$this->l('Selected').'<br/>
									<table>
										<tr>
											<td valign="top">
												<select multiple="multiple" size="12" name="hours" disabled="disabled">
													<option value="0" >00</option>
													<option value="1" >01</option>
													<option value="2" >02</option>
													<option value="3" >03</option>
													<option value="4" >04</option>
													<option value="5" >05</option>
													<option value="6" >06</option>
													<option value="7" >07</option>
													<option value="8" >08</option>
													<option value="9" >09</option>
													<option value="10" >10</option>
													<option value="11" >11</option>
												</select>
											</td>
											<td valign="top">
												<select multiple="multiple" size="12" name="hours" disabled="disabled">
													<option value="12" >12</option>
													<option value="13" >13</option>
													<option value="14" >14</option>
													<option value="15" >15</option>
													<option value="16" >16</option>
													<option value="17" >17</option>
													<option value="18" >18</option>
													<option value="19" >19</option>
													<option value="20" >20</option>
													<option value="21" >21</option>
													<option value="22" >22</option>
													<option value="23" >23</option>
												</select>
											</td>
										</tr>
									</table>
								</td>
								<td valign="top">
									<input type="radio" name="all_days" value="1"  checked="checked" onclick="enable_cron_fields(\'days\', form, 0)"/> '.$this->l('All').'<br/>
									<input type="radio" name="all_days" value="0"  onclick="enable_cron_fields(\'days\', form, 1)"/> '.$this->l('Selected').'<br/>
									<table>
										<tr>
											<td valign="top">
												<select multiple="multiple" size="12" name="days" disabled="disabled">
													<option value="1" >01</option>
													<option value="2" >02</option>
													<option value="3" >03</option>
													<option value="4" >04</option>
													<option value="5" >05</option>
													<option value="6" >06</option>
													<option value="7" >07</option>
													<option value="8" >08</option>
													<option value="9" >09</option>
													<option value="10" >10</option>
													<option value="11" >11</option>
													<option value="12" >12</option>
												</select>
											</td>
											<td valign="top">
												<select multiple="multiple" size="12" name="days" disabled="disabled">
													<option value="13" >13</option>
													<option value="14" >14</option>
													<option value="15" >15</option>
													<option value="16" >16</option>
													<option value="17" >17</option>
													<option value="18" >18</option>
													<option value="19" >19</option>
													<option value="20" >20</option>
													<option value="21" >21</option>
													<option value="22" >22</option>
													<option value="23" >23</option>
													<option value="24" >24</option>
												</select>
											</td>
											<td valign="top">
												<select multiple="multiple" size="7" name="days" disabled="disabled">
													<option value="25" >25</option>
													<option value="26" >26</option>
													<option value="27" >27</option>
													<option value="28" >28</option>
													<option value="29" >29</option>
													<option value="30" >30</option>
													<option value="31" >31</option>
												</select>
											</td>
										</tr>
									</table>
								</td>
								<td valign="top">
									<input type="radio" name="all_months" value="1"  checked="checked" onclick="enable_cron_fields(\'months\', form, 0)"/> '.$this->l('All').'<br/>
									<input type="radio" name="all_months" value="0"  onclick="enable_cron_fields(\'months\', form, 1)"/> '.$this->l('Selected').'<br/>
									<table>
										<tr>
											<td valign="top">
												<select multiple="multiple" size="12" name="months" disabled="disabled">
													<option value="1" >'.$this->l('January').'</option>
													<option value="2" >'.$this->l('February').'</option>
													<option value="3" >'.$this->l('March').'</option>
													<option value="4" >'.$this->l('April').'</option>
													<option value="5" >'.$this->l('May').'</option>
													<option value="6" >'.$this->l('June').'</option>
													<option value="7" >'.$this->l('July').'</option>
													<option value="8" >'.$this->l('August').'</option>
													<option value="9" >'.$this->l('September').'</option>
													<option value="10" >'.$this->l('October').'</option>
													<option value="11" >'.$this->l('November').'</option>
													<option value="12" >'.$this->l('December').'</option>
												</select>
											</td>
										</tr>
									</table>
								</td>
								<td valign="top">
									<input type="radio" name="all_weekdays" value="1"  checked="checked" onclick="enable_cron_fields(\'weekdays\', form, 0)"/> '.$this->l('All').'<br/>
									<input type="radio" name="all_weekdays" value="0"  onclick="enable_cron_fields(\'weekdays\', form, 1)"/> '.$this->l('Selected').'<br/>
									<table>
										<tr>
											<td valign="top">
												<select multiple="multiple" size="7" name="weekdays" disabled="disabled">
													<option value="0" >'.$this->l('Sunday').'</option>
													<option value="1" >'.$this->l('Monday').'</option>
													<option value="2" >'.$this->l('Tuesday').'</option>
													<option value="3" >'.$this->l('Wednesday').'</option>
													<option value="4" >'.$this->l('Thursday').'</option>
													<option value="5" >'.$this->l('Friday').'</option>
													<option value="6" >'.$this->l('Saturday').'</option>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
				<p><input type="submit" class="button" name="submitAddCron" value="'.$this->l('Save').'" /></p>
			</form>
		';
		return $output;
    }
	
	private function _postProcess($token) {
		global $currentIndex;
		
		if ($id_cron = Tools::getValue('delete')) {
			if ($this->deleteCronByID($id_cron))
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&conf=1&token='.$token);
		}
		
		if ($id_cron_url = Tools::getValue('delete_url')) {
			if ($this->deleteCronURLByID($id_cron_url))
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&conf=1&token='.$token);
		}

		if (Tools::isSubmit('saveSettings')) {
			Configuration::updateValue('cron_method', Tools::getValue('cron_method'));
					
			$cron_test = $this->cronExists($this->id, 'test');
			if ($cron_test != Tools::getValue('cron_test')) {
				if (Tools::getValue('cron_test'))
					$this->addTest();
				else
					$this->deleteTest();
			}
			if (empty($this->_postErrors))
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&conf=6&token='.$token);
		}
		
		if (Tools::isSubmit('submitAddCron')) {
			$this->addCronURL(Tools::getValue('cron_url'), Tools::getValue('cron_mhdmd'));
			if (empty($this->_postErrors))
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&conf=3&token='.$token);
		}
		
		return $output;
	}
	
	private function _displayList($token) {
		global $currentIndex, $cookie;
		$output = '';
		// get the jobs
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'cron`';
		$crons = Db::getInstance()->executeS($sql);
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'cron_url`';
		$crons_url = Db::getInstance()->executeS($sql);
		if ($crons || $crons_url) {
			$output .= '
				<fieldset width="900px" class="space">
					<legend>'.$this->l('Crons jobs').'</legend>
					<table class="table">';
			if ($crons) {
				$output .= '
						<tr>
							<th>'.$this->l('Module').'</th>
							<th>'.$this->l('Method').'</th>
							<th>'.$this->l('Schedule').'</th>
							<th>'.$this->l('Last execution').'</th>
							<th>'.$this->l('Action').'</th>
						</tr>';
						
				foreach ($crons as $cron) {
					$module = Db::getInstance()->GetRow('
						SELECT `name`
						FROM `'._DB_PREFIX_.'module`
						WHERE `id_module` = '.intval($cron['id_module']));
					$output .= '
						<tr>
							<td>'.$module['name'].'</td>
							<td>'.$cron['method'].'</td>
							<td>'.$cron['mhdmd'].'</td>
							<td>'.($cron['last_execution']?Tools::displayDate(date('Y-m-d H:i:s',$cron['last_execution']), $cookie->id_lang, true):$this->l('Never')).'</td>
							<td><a href="'.$currentIndex.'&amp;configure='.$this->name.'&amp;token='.$token.'&amp;delete='.((int) $cron['id_cron']).'"><img src="../img/admin/delete.gif" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a></td>
						</tr>
					';
				}
			}
			if ($crons_url) {
				$output .= '
						<tr>
							<th colspan="2">'.$this->l('Url').'</th>
							<th>'.$this->l('Schedule').'</th>
							<th>'.$this->l('Last execution').'</th>
							<th>'.$this->l('Action').'</th>
						</tr>';
						
				foreach ($crons_url as $cron) {
					$output .= '
						<tr>
							<td colspan="2">'.$cron['url'].'</td>
							<td>'.$cron['mhdmd'].'</td>
							<td>'.($cron['last_execution']?Tools::displayDate(date('Y-m-d H:i:s',$cron['last_execution']), $cookie->id_lang, true):$this->l('Never')).'</td>
							<td><a href="'.$currentIndex.'&amp;configure='.$this->name.'&amp;token='.$token.'&amp;delete_url='.((int) $cron['id_cron_url']).'"><img src="../img/admin/delete.gif" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a></td>
						</tr>
					';
				}
			}
			$output .= '
					</table>
				</fieldset>';
		}
		return $output;
	}

	/**
	 * add a false picture to run task in background
	**/
	public function hookFooter($params)
	{
		if (Configuration::get('cron_method') == 'traffic' &&
			(!Configuration::get('cron_lasttime') ||
			 (Configuration::get('cron_lasttime') + 60 <= time())
			))
			return '<img src="'.$this->_path.'cron_traffic.php?time='.time().'" alt="cron module by samdha.net" width="0" height="0" style="border:none;margin:0; padding:0"/>';
	}
	
	/**
	 * add a cron job
	 * 
	 * usage Module::getInstanceByName('cron')->addCron($this->id, 'myMethod', '5 * * * *');
	 *
	 * $mhdmd details :
	 * .---------------- minute (0 - 59) 
	 * |  .------------- hour (0 - 23)
	 * |  |  .---------- day of month (1 - 31)
	 * |  |  |  .------- month (1 - 12) 
	 * |  |  |  |  .---- day of week (0 - 6) (Sunday=0 )
	 * |  |  |  |  |
	 * *  *  *  *  *
	 * 
	 * @param int $id_module Module ID
	 * @param string $method method of the module to call
	 * @param string $mhdmd when call this cron
	 * @return boolean
	**/
	public function addCron($id_module, $method, $mhdmd = '0 * * * *') {
		if (!$this->active)
			return false;
		require_once(dirname(__FILE__).'/CronParser.php');
		if (!$module = Module::getInstanceById($id_module)) {
			$this->_postErrors[] = $this->l('This module doesn\'t exists.');
			return false;
		}
		$classMethods = array_map('strtolower', get_class_methods($module));
		if (!$classMethods || !in_array(strtolower($method), $classMethods)) {
			$this->_postErrors[] = $this->l('This method doesn\'t exists.');
			return false;
		}
		$cronParser = new CronParser();
		if (!$cronParser->calcLastRan($mhdmd)) {
			$this->_postErrors[] = $this->l('This shedule isn\'t valide.');
			return false;
		}
		
		$values = array(
						'id_module' => intval($id_module),
						'method' => pSQL($method),
						'mhdmd' => pSQL($mhdmd),
						'last_execution' => 0
					   );
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'cron', $values, 'INSERT');
	}
	
	/**
	 * delete a cron job
	 *
	 * @param int $id_module Module ID
	 * @param string $method method of the module to call
	 * @return boolean
	**/
	public function deleteCron($id_module, $method) {
		if (!$this->active)
			return false;
		return Db::getInstance()->delete(_DB_PREFIX_.'cron','`id_module` = '.intval($id_module).' AND `method` = \''.pSQL($method).'\'');		
	}
	
	/**
	 * delete a cron job
	 *
	 * @param int $id_cron cron job ID
	 * @return boolean
	**/
	public function deleteCronByID($id_cron) {
		if (!$this->active)
			return false;
		return Db::getInstance()->delete(_DB_PREFIX_.'cron','`id_cron` = '.intval($id_cron));		
	}

	/**
	 * test if a cron job exists
	 *
	 * @param int $id_module Module ID
	 * @param string $method method of the module to call
	 * @return boolean
	**/
	public function cronExists($id_module, $method) {
		if (!$this->active)
			return false;
		$sql = '
			SELECT id_cron
			FROM `'._DB_PREFIX_.'cron`
			WHERE `id_module` = '.intval($id_module).' AND `method` = \''.pSQL($method).'\'';
		$cron = Db::getInstance()->getRow($sql);
		return is_array($cron);
	}
	
	/**
	 * add a cron job
	 * 
	 * usage Module::getInstanceByName('cron')->addURLCron($url', '5 * * * *');
	 *
	 * $mhdmd details :
	 * .---------------- minute (0 - 59) 
	 * |  .------------- hour (0 - 23)
	 * |  |  .---------- day of month (1 - 31)
	 * |  |  |  .------- month (1 - 12) 
	 * |  |  |  |  .---- day of week (0 - 6) (Sunday=0 )
	 * |  |  |  |  |
	 * *  *  *  *  *
	 * 
	 * @param string $url url to visit
	 * @param string $mhdmd when call this cron
	 * @return boolean
	**/
	public function addCronURL($url, $mhdmd = '0 * * * *') {
		if (!$this->active)
			return false;
		require_once(dirname(__FILE__).'/CronParser.php');
		$cronParser = new CronParser();
		if (!$cronParser->calcLastRan($mhdmd)) {
			$this->_postErrors[] = $this->l('This shedule isn\'t valide.');
			return false;
		}
		
		$values = array(
						'url' => pSQL($url),
						'mhdmd' => pSQL($mhdmd),
						'last_execution' => 0
					   );
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'cron_url', $values, 'INSERT');
	}
	
	/**
	 * delete a cron job
	 *
	 * @param int $id_module Module ID
	 * @param string $method method of the module to call
	 * @return boolean
	**/
	public function deleteCronURL($url) {
		if (!$this->active)
			return false;
		return Db::getInstance()->delete(_DB_PREFIX_.'cron_url','`url` = \''.pSQL($url).'\'');		
	}
	
	/**
	 * delete a cron job
	 *
	 * @param int $id_cron cron job ID
	 * @return boolean
	**/
	public function deleteCronURLByID($id_cron_url) {
		if (!$this->active)
			return false;
		return Db::getInstance()->delete(_DB_PREFIX_.'cron_url','`id_cron_url` = '.intval($id_cron_url));		
	}

	/**
	 * test if a cron job exists
	 *
	 * @param int $id_module Module ID
	 * @param string $method method of the module to call
	 * @return boolean
	**/
	public function cronURLExists($url) {
		if (!$this->active)
			return false;
		$sql = '
			SELECT id_cron_url
			FROM `'._DB_PREFIX_.'cron_url`
			WHERE `url` = \''.pSQL($url).'\'';
		$cron = Db::getInstance()->getRow($sql);
		return is_array($cron);
	}

	/**
	 * execute cron jobs
	 * invalide job will be deleted
	 *
	 * @return void
	**/
	public function runJobs() {
		if ($this->active &&
			(Configuration::get('cron_lasttime') + 60 <= time())) {
			Configuration::updateValue('cron_lasttime', time());
			require_once(dirname(__FILE__).'/CronParser.php');
			
			$cronParser = new CronParser();
			// get the jobs
			$sql = 'SELECT * FROM `'._DB_PREFIX_.'cron`';
			$crons = Db::getInstance()->executeS($sql);
			foreach ($crons as $cron) {
				// When the job should have been executed for the last time ?
				// if it's in the past execute it
				$cronParser->calcLastRan($cron['mhdmd']);
				var_dump($cron['mhdmd'], date('r', $cronParser->getLastRanUnix()), date('r', $cron['last_execution']));
				if ($cronParser->getLastRanUnix() > $cron['last_execution']) {
					// if module doesn't exists delete job
					if (!$module = Module::getInstanceById($cron['id_module'])) {
						$this->deleteCron($cron['id_module'], $cron['method']);
					}
					else {
						$classMethods = array_map('strtolower', get_class_methods($module));
						// if method doesn't exists delete job
						if (!$classMethods || !in_array(strtolower($cron['method']), $classMethods)) {
							$this->deleteCron($cron['id_module'], $cron['method']);
						}
						else {
							$values = array(
								'last_execution' => time()
							);
							Db::getInstance()->autoExecute(_DB_PREFIX_.'cron', $values, 'UPDATE', 'id_cron = '.$cron['id_cron']);
							// run job TODO: make it asynchronous
							call_user_func(array($module, $cron['method']));
						}
					}
				}
			}
			
			// get the url to visit
			$sql = 'SELECT * FROM `'._DB_PREFIX_.'cron_url`';
			$crons = Db::getInstance()->executeS($sql);
			foreach ($crons as $cron) {
				// When the job should have been executed for the last time ?
				// if it's in the past execute it
				$cronParser->calcLastRan($cron['mhdmd']);
				if ($cronParser->getLastRanUnix() > $cron['last_execution']) {
					$values = array(
						'last_execution' => time()
					);
					Db::getInstance()->autoExecute(_DB_PREFIX_.'cron_url', $values, 'UPDATE', 'id_cron_url = '.$cron['id_cron_url']);
					// run job TODO: make it asynchronous
					@file_get_contents($cron['url']);
				}
			}
			
		}
	}
	
	public function getHttpHost($http = false, $entities = false)
	{
		$host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
		if ($entities)
			$host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
		if ($http)
			$host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$host;
		return $host;
	}	
	
	/**
	 * tests method
	 * to show how to add/delete jobs
	**/
	public function addTest() {
		Configuration::deleteByName('cron_lasttest');
		return Module::getInstanceByName('cron')->addCron($this->id, 'test', '* * * * *');
	}
	public function deleteTest() {
		Configuration::deleteByName('cron_lasttest');
		return Module::getInstanceByName('cron')->deleteCron($this->id, 'test');
	}
	public function test() {
		// store the last time the test was executed
		return Configuration::updateValue('cron_lasttest', time());
	}
}
}
?>
