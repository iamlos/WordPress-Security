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
	 * Placeholder for htpasswd account
	 * @var array
	 */
	private $htpasswd = array();

	/**
	 * Perform all tasks
	 */
	public function __construct($htpasswd = array())
	{
		$this->remove_default_files();
		$this->turn_off_autocomplete();

		if (isset($htpasswd['username'], $htpasswd['password']))
		{
			$this->set_htpasswd_account($htpasswd);
			$this->protect_wp_login();
		}
		
		$this->self_remove();
	}

	/**
	 * Set an account to access wp-login.php
	 * @param string $username
	 * @param string $password
	 */
	public function set_htpasswd_account($htpasswd)
	{
		if ( ! is_array($htpasswd))
			return;

		$this->htpasswd['username'] = $htpasswd['username'];
		$this->htpasswd['password'] = $htpasswd['password'];
	}

	/**
	 * Change some line of code to turn off auto-fill in wp-login.php
	 */
	public function turn_off_autocomplete()
	{
		$wp_login = BASE_PATH . 'wp-login.php';
		if ( ! file_exists($wp_login))
			return;

		$org_source = file_get_contents($wp_login);

		// Edit Username Input
		$search = 'name="log" id="user_login"';
		$replace = 'name="log" autocomplete="off" id="user_login"';
		if (FALSE !== strpos($org_source, $search))
		{
			$new_source = str_replace($search, $replace, $org_source);
		}
		else
		{
			// Prevent error: "Undefined variable: new_source..."
			$new_source = $org_source;
		}
		
		// Edit Password Input
		$search = 'name="pwd" id="user_pass"';
		$replace = 'name="pwd" autocomplete="off" id="user_pass"';
		if (FALSE !== strpos($new_source, $search))
			$new_source = str_replace($search, $replace, $new_source);

		// Update wp-login.php
		if (isset($new_source))
		{
			file_put_contents($wp_login, $new_source);
			unset($new_source);
		}

		unset($search, $replace, $org_source);
	}

	/**
	 * Remove all the files will be scanned by CMSmap
	 */
	public function remove_default_files()
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
	public function self_remove()
	{
		// Remove runner
		@unlink(BASE_PATH . 'wp-security-run.php');
		
		if (@unlink(__FILE__))
			exit('All tasks have been completed!');
	}

	/**
	 * Generate .htaccess and .htpasswd to protect wp-login.php
	 * @return bool Returns TRUE if generate files successfully. Otherwise returns FALSE.
	 */
	public function protect_wp_login()
	{
		// Make sure we have an account
		if (count($this->htpasswd) == 0)
			return FALSE;
		
		$hta = '';
		$br = "\n";

		// If user have run this file before,
		// we don't need to create any files again
		$path = BASE_PATH . '.htaccess';
		if (file_exists($path))
		{
			$org_hta = file_get_contents($path);
			if (FALSE !== strpos($org_hta, 'Protected by WP Security'))
				return TRUE;
			else
				$hta = $org_hta . $br; // To append in next step.
		}

		// .htaccess template
		$hta .= 'ErrorDocument 401 default' . $br;
		$hta .= '<FilesMatch wp-login.php>' . $br;
		$hta .= 'AuthType Basic' . $br;
		$hta .= 'AuthName "Protected by WP Security"' . $br;
		$hta .= 'AuthUserFile ' . BASE_PATH . '.htpasswd' . $br;
		$hta .= 'Require valid-user' . $br;
		$hta .= '</FilesMatch>' . $br;

		// Write .htaccess
		file_put_contents($path, $hta);

		// Encrypt password for .htpasswd
		$password = $this->crypt_apr1_md5($this->htpasswd['password']);

		$htp = $this->htpasswd['username'] . ':' . $password;

		// Write .htpasswd
		$path2 = BASE_PATH . '.htpasswd';
		file_put_contents($path2, $htp);

		return (file_exists($path) && file_exists($path2));
	}

	/**
	 * APR1-MD5 encryption method (windows compatible)
	 * @param  string $string Plain password
	 * @return string         Encrypted password
	 * @link http://stackoverflow.com/a/8786956/387247
	 */
	private function crypt_apr1_md5($string)
	{
	    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
	    $len = strlen($string);
	    $text = $string.'$apr1$'.$salt;
	    $bin = pack("H32", md5($string.$salt.$string));
	    for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
	    for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $string{0}; }
	    $bin = pack("H32", md5($text));
	    for($i = 0; $i < 1000; $i++)
	    {
	        $new = ($i & 1) ? $string : $bin;
	        if ($i % 3) $new .= $salt;
	        if ($i % 7) $new .= $string;
	        $new .= ($i & 1) ? $bin : $string;
	        $bin = pack("H32", md5($new));
	    }
	    $tmp = '';
	    for ($i = 0; $i < 5; $i++)
	    {
	        $k = $i + 6;
	        $j = $i + 12;
	        if ($j == 16) $j = 5;
	        $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
	    }
	    $tmp = chr(0).chr(0).$bin[11].$tmp;
	    $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
	    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
	    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
	 
	    return "$"."apr1"."$".$salt."$".$tmp;
	}
}

/* Coded by Juno_okyo */