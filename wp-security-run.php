<?php
if ( ! defined('BASE_PATH'))
	define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

$core = BASE_PATH . 'wp-security.php';
if ( ! file_exists($core))
	exit('Error: wp-security.php was not found!');

require_once $core;

$htpasswd = array(
	'username' => 'YOUR_USERNAME',
	'password' => 'YOUR_PASSWORD'
);

$runner = new WP_SEC($htpasswd);

/* Coded by Juno_okyo */