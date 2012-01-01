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