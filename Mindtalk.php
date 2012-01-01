<?php
/*
 * Mindtlak PHP oAuth Library
 *
 * @package		Digaku API Library
 * @author		Dwi Setiyadi / @dwisetiyadi
 * @license		http://www.gnu.org/licenses/gpl.html
 * @link		http://dwi.web.id
 * @version		2.0
 * Last changed	23 Nov, 2011
 * Endpoint URL revision for updated Mindtalk oAuth by Surya Djayadi
 */

// ------------------------------------------------------------------------

if (!function_exists('curl_init')) {
	die('Mindtalk.php needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
	die('Mindtalk.php needs the JSON PHP extension.');
}
if (!function_exists('simplexml_load_string')) {
	die('Mindtalk.php needs the simplexml_load_string PHP extension.');
}
if (!class_exists('DOMDocument')) {
	die('Mindtalk.php needs the DOMDocument PHP extension.');
}

/**
 * This class object
 */
class Mindtalk {
	var $cid;
	var $csecret;
	var $ccode;
	var $lang;
	var $authEndpoint = 'http://auth.mindtalk.com/';
	var $apiEndpoint = 'http://api.mindtalk.com/v1/';
	
	/**
	 * Constructor
	 * Configure API setting
	 */
	function mindtalk($params = array('client_id'=>'', 'client_secret'=>'', 'language'=>'id_ID')) {
		$this->cid = $params['client_id'];
		$this->csecret = $params['client_secret'];
		$this->lang = $params['language'];
	}
	
	// --------------------------------------------------------------------

	/**
	 * Initialize
	 *
	 * Assigns a code value to request access token from Digaku.com
	 *
	 * @access public
	 * @return void
	 */
	function setcode($code = '') {
		$this->ccode = $code;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Autorize
	 *
	 * Authorization process to Digaku.com
	 *
	 * @access public
	 * @return void
	 */
	function authorize($redirect_uri = '') {
		return trim($this->authEndpoint, '/').'/authorize?client_id='.$this->cid.'&redirect_uri='.$redirect_uri;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Checking
	 *
	 * Checking any access token for login state
	 *
	 * @access public
	 * @return boolean
	 */
	function checklogin() {
		libxml_use_internal_errors( true );
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->loadXML($this->getcontent(trim($this->authEndpoint, '/').'/access_token?code='.$this->ccode.'&client_secret='.$this->csecret));
		$errors = libxml_get_errors();
		if($errors == array()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Access Token
	 *
	 * Get access token value, have two value.
	 * To access it, use:
	 * [CLASS OBJECT]->accesstoken()->token for access token
	 * [CLASS OBJECT]->accesstoken()->refresh for refresh token code, this is use for refreshing a new access token
	 *
	 * @access public
	 * @parametersboject token refresh
	 * @return string
	 */
	function accesstoken() {
		$request_accesstoken = $this->getcontent(trim($this->authEndpoint, '/').'/access_token?code='.$this->ccode.'&client_secret='.$this->csecret);
		$request_accesstoken = explode('&', $request_accesstoken);
		foreach($request_accesstoken as $key => $value) {
			$value = explode('=', $value);
			$data->$value[0] = $value[1];
		}
		return $data;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Clear Token
	 *
	 * Delete exist token on Digaku.com.
	 *
	 * @access public
	 * @return boolean
	 */
	function logout($access_token_saved = '') {
		if ($access_token_saved == '') {
			$get_token = $this->accesstoken()->access_token;
		} else {
			$get_token = $access_token_saved;
		}
		
		$clear_request = simplexml_load_string($this->getcontent(trim($this->authEndpoint, '/').'/clear_token?access_token='.$get_token));
		if ($clear_request === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Call
	 *
	 * Get user API data. example:
	 * [CLASS OBJECT]->call('my/info', array('key'=>'value'))
	 *
	 * @access public
	 * @parameter 1 API Call
	 * @parameter 2 parameter for api call
	 * @parameter 3 access token
	 * @return object
	 */
	function call($call = '', $params = array(), $access_token_saved = '') {
		if ($access_token_saved == '') {
			if (isset($this->accesstoken()->access_token)) {
				$get_token = $this->accesstoken()->access_token;
			} else {
				$get_token = FALSE;
			}
		} else {
			$get_token = $access_token_saved;
		}
		
		$params_string = '';
		foreach ($params as $key=>$value) {
			$params_string .= '&'.$key.'='.urlencode($value);
		}
		if ( ! isset($params['rf'])) $params_string .= '&rf=json';
		if ( ! isset($params['itl'])) $params_string .= '&itl='.$this->lang;
		
		$api_data = $this->getcontent(trim($this->apiEndpoint, '/').'/'.$call.'?access_token='.$get_token.$params_string);
		if ($api_data != '') {
			return json_decode(json_encode(array('status'=>'200', 'content'=>json_decode($api_data))));
		}
		
		return json_decode(json_encode(array('status'=>'409', 'content'=>'error')));
	}
	
	// --------------------------------------------------------------------

	/**
	 * Post
	 *
	 * Posting API data. example:
	 * [CLASS OBJECT]->post('user/register', array('key'=>'value'))
	 *
	 * @access public
	 * @parameter 1 API Call
	 * @parameter 2 parameter for api call
	 * @parameter 3 access token
	 * @return object
	 */	
	function post($call = '', $params = array(), $access_token_saved = '') {
		if ($access_token_saved == '') {
			if (isset($this->accesstoken()->access_token)) {
				$get_token = $this->accesstoken()->access_token;
			} else {
				$get_token = FALSE;
			}
		} else {
			$get_token = $access_token_saved;
		}
		
		$url = trim($this->apiEndpoint, '/').'/'.$call;
		$useragent = "MindTalk-PHP";
		
		if ($get_token) {
			$params_string = '&access_token='.$get_token;
		} else {
			$params_string = '';
		}
		foreach($params as $key=>$value) {
			$params_string .= '&'.$key.'='.urlencode($value);
		}
		$params_string = trim($params_string,'&');
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($params));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		ob_start();
		$result = curl_exec($ch);
		if ($result === false) $result = curl_error($ch);
		ob_end_clean();
		curl_close($ch);
		
		$result = json_decode($result, true);
		$result = json_decode(json_encode($result));
		
		return $result;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Page content
	 *
	 * Get return value from Digaku.com page
	 *
	 * @access private
	 * @parameters url
	 * @return string
	 */
	private function getcontent($url = '') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}
?>