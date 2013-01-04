<?php

class kekoliRequest {

     ////////////////////////////////////////////////////
     // Utilities

     /* Borrowed from rom OAuthUtil */
     public static function urlencode_rfc3986($input) {
          if (is_array($input)) {
               return array_map(array('kekoliRequest', 'urlencode_rfc3986'), $input);
          } else if (is_scalar($input)) {
               return str_replace(
                               '+', ' ', str_replace('%7E', '~', rawurlencode($input))
               );
          } else {
               return '';
          }
     }

     public static function build_http_query($params) {
          if (!$params) {
               return '';
          }

          // Urlencode both keys and values
          $keys = kekoliRequest::urlencode_rfc3986(array_keys($params));
          $values = kekoliRequest::urlencode_rfc3986(array_values($params));
          $params = array_combine($keys, $values);

          // Parameters are sorted by name, using lexicographical byte value ordering.
          // Ref: Spec: 9.1.1 (1)
          uksort($params, 'strcmp');

          $pairs = array();
          foreach ($params as $parameter => $value) {
               if (is_array($value)) {
                    // If two or more parameters share the same name, they are sorted by their value
                    // Ref: Spec: 9.1.1 (1)
                    // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
                    sort($value, SORT_STRING);
                    foreach ($value as $duplicate_value) {
                         $pairs[] = $parameter . '=' . $duplicate_value;
                    }
               } else {
                    $pairs[] = $parameter . '=' . $value;
               }
          }
          // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
          // Each name-value pair is separated by an '&' character (ASCII code 38)
          return implode('&', $pairs);
     }

     /* End borrowing */

     public static function signe($url, $method, $params, $Config) {
          $data = implode('&', array(
              $method,
              urlencode($url),
              urlencode(kekoliRequest::build_http_query($params))
                  ));
          $key = implode('&', array($Config['KeKoli_Consumer_secret'], $Config['KeKoli_Token_secret']));
          $params['signature'] = base64_encode(hash_hmac('sha1', $data, $key, true));
          return $url . '?' . kekoliRequest::build_http_query($params);
     }

     ////////////////////////////////////////////////////
     // Services

     /**
      * Execute ping service
      * @return text
      * @param $Config array $Config['KeKoli_Consumer_key'] $this->Config['KeKoli_Consumer_secret'] $Config['KeKoli_Token'] $Config['KeKoli_Token_secret']
      */
     public function Ping($Config) {
          $url_host = 'www.kekoli.com';		  
          $url_service = '/api/ping.json';
          $url = 'http://' . $url_host . $url_service;

          $params = array(
              'consumer_key' => $Config['KeKoli_Consumer_key'],
              'token' => $Config['KeKoli_Token'],
              'timestamp' => time(),
              'nonce' => substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, 5),
          );

          $full_url = kekoliRequest::signe($url, 'GET', $params, $Config);

          $ch = curl_init($full_url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $data = curl_exec($ch);
          $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);

          return array($return_code, json_decode($data, true), $url_host);
     }

     /**
      * Send orders to kekoli
      * @return text
      * @param $orders_csv
      */
     public function SendOrders($Config, $orders_csv) {
          $url_host = 'www.kekoli.com';		  
          $url_service = '/api/colis.json';
          $url = 'http://' . $url_host . $url_service;

          $params = array(
              'consumer_key' => $Config['KeKoli_Consumer_key'],
              'token' => $Config['KeKoli_Token'],
              'timestamp' => time(),
              'nonce' => substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, 5)
          );
          $full_url = kekoliRequest::signe($url, 'POST', $params, $Config);

          $tmpfname = tempnam(dirname(__FILE__), "kekoli_");
          $handle = fopen($tmpfname, "w");
          fwrite($handle, $orders_csv);
          fclose($handle);

          $ch = curl_init($full_url);
          //echo '$full_url : '.$full_url;
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_POST, 1);
          $posts = array('data' => '@' . $tmpfname);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
          $data = curl_exec($ch);
          //print_r(curl_getinfo($ch));
          $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          unlink($tmpfname);
          return array($return_code, json_decode($data, true), $url_host);
     }

	 /**
      * Get orders done from kekoli
      * @return array
      */
     public function GetOrders($Config) {
          $url_host = 'www.kekoli.com';		  
          $url_service = '/api/colis.json';
          $url = 'http://' . $url_host . $url_service;

          $params = array(
              'consumer_key' => $Config['KeKoli_Consumer_key'],
              'token' => $Config['KeKoli_Token'],
              'timestamp' => time(),
              'nonce' => substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, 5),
			  'etat' => 'fin'
          );

          $full_url = kekoliRequest::signe($url, 'GET', $params, $Config);

          $ch = curl_init($full_url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $data = curl_exec($ch);
          $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
		 
          return array($return_code, json_decode($data, true), $url_host);
     }
	 
}
