# Mindtalk API - PHP Library

Since Digaku changed became Mindtalk, so we need to make a new library (for changed and deprecated API).

### AVAILABLE API
* Call API (Authentic API)
* Post API (Authentic API)

Please see Web API documentation [http://developer.digaku.com/api/wiki/APIResources](http://developer.digaku.com/api/wiki/APIResources "API Resource")

### How to (sorry my english is very bad):

* Create application on [https://auth.mindtalk.com/ui/client/create](https://auth.mindtalk.com/ui/client/create "Create an Application on Mindtalk") and get your client id and client secret
* Download Mindtalk.php from this page and put on your app folder, if you use Codeigniter, put on [YOUR_APP]/application/libraries/
* Create your application

### SINGLE SIGN ON (you may need to read this http://oauth.net/)
After authorize proccess, Mindtalk.com API will be redirect to your callback url given and add a CODE variable on url (variable GET). And then assign the CODE variable to setcode() function object, example: $mindtalk->setcode($_GET['code']). The CODE variable is required to get access token, so may be you will need to save it on session, cookie, or database. Without the CODE variable, you can't request access token, and without access token you can't access API data.

	<?php
	//start session to save callback $_GET['code'] from digaku after authorize, this is optional, depend on your method
	session_start();

	//call library
	include('Mindtalk.php');

	//make configuration
	$config['client_id'] = ''; //or API Key, required
	$config['client_secret'] = ''; //required
	$config['language'] = ''; //optional id_ID or en_US, default id_ID

	// Assign class object with configuration to $mindtalk
	// Use $this->load->library('mindtalk', $config); for Codeigniter
	$mindtalk = new Mindtalk($config);

	// Your application callback
	$url_callback = '';

	// Check $_GET['code'] and save it in session
	if (isset($_GET['code'])) {
		if (isset($_SESSION['digaku_sess_code'])) {
			unset($_SESSION['digaku_sess_code']);
			$_SESSION['digaku_sess_code'] = $_GET['code'];
		} else {
			$_SESSION['digaku_sess_code'] = $_GET['code'];
		}
	}

	// if the session with $_GET['code'] content available, set the value to get access token
	if (isset($_SESSION['digaku_sess_code'])) {
		$mindtalk->setcode($_SESSION['digaku_sess_code']);
	}

	// Clear tokken if any request via $_GET['keluar']
	if (isset($_GET['keluar'])) {
		$mindtalk->logout();
		session_destroy();
	}

	// Check for login state and get the API data
	if ($mindtalk->checklogin()) {
		//link logout
		echo '<a href="'.$url_callback.'?keluar=out">Logout</a><br />';
	
		//access token value
		echo 'Access token: '.$mindtalk->accesstoken()->access_token.'<br />';
	
		//refresh access token code
		echo 'Refresh token: '.$mindtalk->accesstoken()->refresh_token;
	
		echo '<h3>User Info</h3>';
		echo '<pre>';
		print_r($mindtalk->call('my/info'));
		echo '</pre>';
	
		echo '<h3>Streams</h3>';
		echo '<pre>';
		$data['limit'] = 5;
		print_r($mindtalk->call('my/stream',$data));
		echo '</pre>';
	} else {
		echo '<a href="'.$mindtalk->authorize($url_callback).'">Login</a><br>';
	}
	?>