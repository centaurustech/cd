<?php
/**
 * @module		sendsms
 * @author		Yann Bonnaillie
 * @copyright	Yann Bonnaillie
 **/

define('DOMAIN','www.smsworldsender.com');
define('PATH','/http_sms_presta.php');
define('PATH_NB','/http_sms_number_presta.php');
define('PATH_RSS','/http_rss.php');

define('INSTANTANE', 1);
define('DIFFERE', 2);

class SMS
{
	 private $smsLogin; 	// string
	 private $smsPassword; 	// string
	 private $prestaKey; 	// string

	 private $sms_text; // string

 	 private $t_nums; 	// array
 	 private $t_fields_1; 	// array
 	 private $t_fields_2; 	// array
 	 private $t_fields_3; 	// array

	 private $type; 	// int
	 private $d; 	// int
 	 private $m; 	// int
 	 private $h; 	// int
 	 private $i; 	// int
 	 private $y; 	// int

	 private $sender;  	// string
	 private $simulation; 	// int

	 private $list_name; // string

	 public function __construct()
	 {
		$this->smsLogin = '';
		$this->smsPassword = '';
		$this->prestaKey = '';

		$this->sms_text = '';

		$this->t_nums = array();
		$this->t_fields_1 = array();
		$this->t_fields_2 = array();
		$this->t_fields_3 = array();

		$this->type = INSTANTANE;
 		$this->d = 0;
 		$this->m = 0;
 		$this->h = 0;
 		$this->i = 0;
 		$this->y = 0;

 		$this->sender 	= 'SWS';
 		$this->list_name = 'liste_main';
 		$this->simulation 	= 0;
	}

	public function send()
	{
		$domain = DOMAIN;
		$path = PATH;

		$data = array(
			'smsLogin'     	=> $this->smsLogin,
			'smsPassword'	=> $this->smsPassword,
			'prestaKey'		=> $this->prestaKey,
			'sms_text'		=> $this->sms_text,
			'destsNums'		=> implode(',', $this->t_nums),
			't_fields_1'	=> implode(',', $this->t_fields_1),
			't_fields_2'	=> implode(',', $this->t_fields_2),
			't_fields_3'	=> implode(',', $this->t_fields_3),
			'type'			=> $this->type,
			'simulation'	=> $this->simulation,
			'sender'		=> $this->sender,
			'list_name' 	=> $this->list_name,
			'domain'		=> $domain,
			'path'			=> $path
			);

		if ($this->type == DIFFERE)
		{
			$data['sending_y']	= $this->y;
			$data['sending_d']	= $this->d;
			$data['sending_m']	= $this->m;
			$data['sending_h']	= $this->h;
			$data['sending_i']	= $this->i;
		}

		return trim($this->httpPost($data));
	}

	public function number()
	{
		$domain = DOMAIN;
		$path = PATH_NB;

		$data = array(
			'smsLogin'     	=> $this->smsLogin,
			'smsPassword'	=> $this->smsPassword,
			'prestaKey'		=> $this->prestaKey,
			'domain'		=> $domain,
			'path'			=> $path
			);
		return trim($this->httpPost($data));
	}

	public function rss()
	{
		$domain = DOMAIN;
		$path = PATH_RSS;

		$data = array(
			'smsLogin'     	=> $this->smsLogin,
			'smsPassword'	=> $this->smsPassword,
			'prestaKey'		=> $this->prestaKey,
			'domain'		=> $domain,
			'path'			=> $path
			);
		return trim($this->httpPost($data));
	}

	/* function status
	 * param string $request_type = ('notify' || 'queue')
	 * param string $sent_sms_id = ticket d'envoi rendu ors d'un envoi effectué avec succès.
	 */
	public function status($request_type, $sent_sms_id)
	{
		$domain = DOMAIN;
		$path = PATH_STATUS;

		$data = array(
			'smsLogin'     => $this->smsLogin,
			'smsPassword'	=> $this->smsPassword,
			'prestaKey'		=> $this->prestaKey,
			'request_type'	=> $request_type,
			'sent_sms_id'	=> $sent_sms_id,
			'domain'		=> $domain,
			'path'			=> $path
			);

		return trim($this->httpPost($data));
	}

	public function httpPost($data)
	{
		$response = '';
		$request = http_build_query($data, '', '&');

		if (function_exists('curl_init') && $ch = @curl_init($data['domain'] . $data['path'])) {
			curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            curl_close($ch);
		} else if (ini_get('allow_url_fopen')) {
			if ($fp = @fsockopen($data['domain'], 80)){
				fputs($fp, "POST ".$data['path']." HTTP/1.0\r\n");
				fputs($fp, "Host: ".$data['domain']."\r\n");
				fputs($fp, "User-Agent: Internet Explorer/7 [fr] (Vista; I)\r\n");
				fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
				fputs($fp, "Content-Length: ".strlen($request)."\r\n");
				fputs($fp, "Connection: close\r\n");
				fputs($fp, "\r\n".$request);

				$response = '';
				while (!feof($fp))
					$response .= fgets($fp, 1024);
				fclose($fp);
			}
		} else {
			trigger_error('Server does not support HTTP requests.', E_USER_ERROR);
		}
		if ($response != '') {
			if (strpos($response, "\xEF\xBB\xBF") !== false)
				$response = str_replace("\xEF\xBB\xBF", "", $response);
			return preg_replace("/^.*?\r\n\r\n/us", '', $response);
		} else {
			return 'KO_100_connection failed_0';
		}
	}

	public function setSmsLogin($login)
	{
   		$this->smsLogin = $login;
 	}

	public function setSmsPassword($password)
	{
   		$this->smsPassword = $password;
 	}

 	public function setPrestaKey($prestaKey)
	{
   		$this->prestaKey = $prestaKey;
 	}

	public function setSmsText($text)
	{
   		$this->sms_text = $text;
 	}

 	public function setType($type)
 	{
 		$this->type = $type;
 	}

	public function setNums($nums)
	{
		$this->t_nums = $nums;
	}

	public function setParams1($t_fields)
	{
		$this->t_fields_1 = $t_fields;
	}

	public function setParams2($t_fields)
	{
		$this->t_fields_2 = $t_fields;
	}

	public function setParams3($t_fields)
	{
		$this->t_fields_3 = $t_fields;
	}

	public function setSimulation($simulation)
	{
		$this->simulation = $simulation;
	}

	public function setSender($sender)
	{
		$this->sender = $sender;
	}

	public function setListName($list_name)
	{
		$this->list_name = $list_name;
	}

	public function setDate($y, $d, $m, $h, $i)
	{
		$this->y = $y;
		$this->d = $d;
		$this->m = $m;
		$this->h = $h;
		$this->i = $i;
	}
}
?>