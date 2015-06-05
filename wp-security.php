<?php
if ( ! defined('BASE_PATH'))
	define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/**
 * Protect your WordPress site from Vulnerability Scanners
 * 
 * @package WP Security
 * @author Juno_okyo <junookyo@gmail.com>
 * @copyright 2015 J2TeaM
 * @version 1.0.0
 */
class WP_SEC
{

	/**
	 * Default WP files from CMSmap
	 * @var array
	 */
	private $default_files = array(
		'readme.html',
		'license.txt',
		'license.vi.txt',
		'wp-config-sample.php',
		'wp-includes/images/crystal/license.txt',
		'wp-includes/js/plupload/license.txt',
		'wp-includes/js/plupload/changelog.txt',
		'wp-includes/js/tinymce/license.txt',
		'wp-includes/js/tinymce/plugins/spellchecker/changelog.txt',
		'wp-includes/js/swfupload/license.txt',
		'wp-includes/ID3/license.txt',
		'wp-includes/ID3/readme.txt',
		'wp-includes/ID3/license.commercial.txt',
		'wp-content/themes/twentythirteen/fonts/COPYING.txt',
		'wp-content/themes/twentythirteen/fonts/LICENSE.txt'
	);

	/**
	 * Perform all tasks
	 */
	public function __construct()
	{
		$this->remove_default_files();
		$this->turn_off_autocomplete();
		$this->self_remove();
	}

	/**
	 * Change some line of code to turn off auto-fill in wp-login.php
	 */
	private function turn_off_autocomplete()
	{
		$wp_login = BASE_PATH . 'wp-login.php';
		if ( ! file_exists($wp_login))
			return;

		$org_source = file_get_contents($wp_login);

		// Edit Username Input
		$search = 'name="log" id="user_login"';
		$replace = 'name="log" autocomplete="off" id="user_login"';
		if (FALSE !== strpos($org_source, $search))
			$new_source = str_replace($search, $replace, $org_source);
		
		// Edit Password Input
		$search = 'name="pwd" id="user_pass"';
		$replace = 'name="pwd" autocomplete="off" id="user_pass"';
		if (FALSE !== strpos($new_source, $search))
			$new_source = str_replace($search, $replace, $new_source);

		// Update wp-login.php
		file_put_contents($wp_login, $new_source);

		unset($search, $replace, $org_source, $new_source);
	}

	/**
	 * Remove all the files will be scanned by CMSmap
	 */
	private function remove_default_files()
	{
		foreach ($this->default_files as $file)
		{
			$path = BASE_PATH . $file;
			if (file_exists($path))
				@unlink($path);
		}
	}

	/**
	 * Automatically remove itself when done
	 */
	private function self_remove()
	{
		if (@unlink(__FILE__))
			exit('All tasks have been completed!');
	}
}

/**
 * We need a task runner
 */
$runner = new WP_SEC;

/* Coded by Juno_okyo */