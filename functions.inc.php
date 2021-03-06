<?php
/**
 * PHP Server Monitor
 * Monitor your servers and websites.
 *
 * This file is part of PHP Server Monitor.
 * PHP Server Monitor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP Server Monitor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHP Server Monitor.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     phpservermon
 * @author      Pepijn Over <pep@peplab.net>
 * @copyright   Copyright (c) 2008-2015 Pepijn Over <pep@peplab.net>
 * @license     http://www.gnu.org/licenses/gpl.txt GNU GPL v3
 * @version     Release: @package_version@
 * @link        http://www.phpservermonitor.org/
 **/

###############################################
#
# Language functions
#
###############################################

/**
 * Retrieve language settings from the selected language file
 *
 * @return string
 * @see psm_load_lang()
 */
function psm_get_lang() {
	$args = func_get_args();

	if (empty($args)) return $GLOBALS['sm_lang'];

	$result = null;
	$resultDefault = null;
	$node = null;
	$nodeDefault = null;

	if ($args) {
		$node = '$GLOBALS[\'sm_lang\'][\'' . implode('\'][\'', $args) . '\']';
		$nodeDefault = '$GLOBALS[\'sm_lang_default\'][\'' . implode('\'][\'', $args) . '\']';
	}

	eval('if (isset(' . $node . ')) $result = ' . $node . ';');
	eval('if (isset(' . $nodeDefault . ')) $resultDefault = ' . $nodeDefault . ';');

	if (empty($result)) {
		return $resultDefault;
	} else {
		return $result;
	}
}

/**
 * Load language from the language file to the $GLOBALS['sm_lang'] variable
 *
 * @param string $lang language
 * @see psm_get_lang()
 */
function psm_load_lang($lang) {
	// if not in the language translation must always be available starting translation - English
	$default_lang_file = PSM_PATH_LANG . 'en_US.lang.php';

	if (file_exists($default_lang_file)) {
		require $default_lang_file;

		if (isset($sm_lang)) {
			$GLOBALS['sm_lang_default'] = $sm_lang;
			unset($sm_lang);
		}
	}

	// translated language
	$lang_file = PSM_PATH_LANG . $lang . '.lang.php';

	if (!file_exists($lang_file)) {
		// If the file has been removed, we use the english one
		$en_file = PSM_PATH_LANG . 'en_US.lang.php';
		if (!file_exists($en_file)) {
			// OK, nothing we can do
			die('unable to load language file: ' . $lang_file);
		}
		$lang_file = $en_file;
	}

	require $lang_file;
	if (isset($sm_lang['locale'])) {
		setlocale(LC_TIME, $sm_lang['locale']);
	}

	$GLOBALS['sm_lang'] = $sm_lang;
}

/**
 * Retrieve a list with keys of the available languages
 *
 * @return array
 * @see psm_load_lang()
 */
function psm_get_langs() {
	$fn_ext = '.lang.php';
	$lang_files = glob(PSM_PATH_LANG . '*' . $fn_ext);
	$langs = array();

	foreach($lang_files as $file) {
		$key = str_replace($fn_ext, '', basename($file));
		$sm_lang = array();
		if(file_exists($file)) {
			require $file;
		}
		if(isset($sm_lang['name'])) {
			$name = $sm_lang['name'];
		} else {
			$name = $key;
		}
		$langs[$key] = $name;
		unset($sm_lang);
	}
	ksort($langs);
	return $langs;
}

/**
 * Get a setting from the config.
 *
 * @param string $key
 * @param mixed $alt if not set, return this alternative
 * @return string
 * @see psm_load_conf()
 */
function psm_get_conf($key, $alt = null) {
	if(!isset($GLOBALS['sm_config'])) {
		psm_load_conf();
	}
	$result = (isset($GLOBALS['sm_config'][$key])) ? $GLOBALS['sm_config'][$key] : $alt;

	return $result;
}

/**
 * Load config from the database to the $GLOBALS['sm_config'] variable
 *
 * @global object $db
 * @return boolean
 * @see psm_get_conf()
 */
function psm_load_conf() {
	global $db;

	$GLOBALS['sm_config'] = array();

	if(!defined('PSM_DB_PREFIX') || !$db->status()) {
		return false;
	}
	if(!$db->ifTableExists(PSM_DB_PREFIX.'config')) {
		return false;
	}
	$config_db = $db->select(PSM_DB_PREFIX . 'config', null, array('key', 'value'));

	if(is_array($config_db) && !empty($config_db)) {
		foreach($config_db as $setting) {
			$GLOBALS['sm_config'][$setting['key']] = $setting['value'];
		}
		return true;
	} else {
		return false;
	}
}

/**
 * Update a config setting.
 *
 * If the key does not exist yet it will be created.
 * @global \psm\Service\Database $db
 * @param string $key
 * @param string $value
 */
function psm_update_conf($key, $value) {
	global $db;

	// check if key exists
	$exists = psm_get_conf($key, false);
	if($exists === false) {
		// add new config record
		$db->save(
			PSM_DB_PREFIX . 'config',
			array(
				'key' => $key,
				'value' => $value,
			)
		);
	} else {
		$db->save(
			PSM_DB_PREFIX.'config',
			array('value' => $value),
			array('key' => $key)
		);
	}
	$GLOBALS['sm_config'][$key] = $value;
}

###############################################
#
# Miscellaneous functions
#
###############################################

/**
 * This function merely adds the message to the log table. It does not perform any checks,
 * everything should have been handled when calling this function
 *
 * @param string $server_id
 * @param string $type
 * @param string $message
 *
 * @return int log_id
 */
function psm_add_log($server_id, $type, $message) {
	global $db;

	return $db->save(
		PSM_DB_PREFIX.'log',
		array(
			'server_id' => $server_id,
			'type' => $type,
			'message' => $message,
		)
	);
}

/**
 * This function just adds a user to the log_users table.
 *
 * @param $log_id
 * @param $user_id
 */
function psm_add_log_user($log_id, $user_id) {
	global $db;

    $db->save(
        PSM_DB_PREFIX . 'log_users',
        array(
            'log_id'  => $log_id,
            'user_id' => $user_id,
        )
    );
}

/**
 * This function adds the result of a check to the uptime table for logging purposes.
 *
 * @param int $server_id
 * @param int $status
 * @param string $latency
 */
function psm_log_uptime($server_id, $status, $latency) {
	global $db;

	$db->save(
		PSM_DB_PREFIX.'servers_uptime',
		array(
			'server_id' => $server_id,
			'date' => date('Y-m-d H:i:s'),
			'status' => $status,
			'latency' => $latency,
		)
	);
}



/**
 * Shortcut to curl_init(), curl_exec and curl_close()
 *
 * @param string $href
 * @param boolean $header return headers?
 * @param boolean $body return body?
 * @param int $timeout connection timeout in seconds. defaults to PSM_CURL_TIMEOUT (10 secs).
 * @param boolean $add_agent add user agent?
 * @param string|bool $website_username Username website
 * @param string|bool $website_password Password website
 * @return string cURL result
 */
function psm_curl_get($href, $header = false, $body = true, $timeout = null, $add_agent = true, $website_username = false, $website_password = false) {
	$timeout = $timeout == null ? PSM_CURL_TIMEOUT : intval($timeout);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, $header);
	curl_setopt($ch, CURLOPT_NOBODY, (!$body));
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_ENCODING, '');

    if($website_username !== false && $website_password !== false && !empty($website_username) && !empty($website_password)) {
        curl_setopt($ch, CURLOPT_USERPWD, $website_username . ":" . $website_password);
    }

	curl_setopt($ch, CURLOPT_URL, $href);

	if($add_agent) {
		curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; phpservermon/'.PSM_VERSION.'; +http://www.phpservermonitor.org)');
	}

	$result = curl_exec($ch);
	curl_close($ch);

	return $result;
}

/**
 * Get a "nice" timespan message
 *
 * Source: http://www.interactivetools.com/forum/forum-posts.php?postNum=2208966
 * @param string $time
 * @return string
 */
function psm_timespan($time) {
	if(empty($time) || $time == '0000-00-00 00:00:00') {
		return psm_get_lang('system', 'never');
	}
	if ($time !== intval($time)) { $time = strtotime($time); }
	if ($time < strtotime(date('Y-m-d 00:00:00')) - 60*60*24*3) {
		$format = psm_get_lang('system', (date('Y') !== date('Y', $time)) ? 'long_day_format' : 'short_day_format');
		// Check for Windows to find and replace the %e
		// modifier correctly
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
			$format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
		}
		return strftime($format, $time);
	}
	$d = time() - $time;
	if ($d >= 60*60*24) {
		$format = psm_get_lang('system', (date('l', time() - 60*60*24) == date('l', $time)) ? 'yesterday_format' : 'other_day_format');
		return strftime($format, $time);
	}
	if ($d >= 60*60*2) { return sprintf(psm_get_lang('system', 'hours_ago'), intval($d / (60*60))); }
	if ($d >= 60*60) { return psm_get_lang('system', 'an_hour_ago'); }
	if ($d >= 60*2) { return sprintf(psm_get_lang('system', 'minutes_ago'), intval($d / 60)); }
	if ($d >= 60) { return psm_get_lang('system', 'a_minute_ago'); }
	if ($d >= 2) { return sprintf(psm_get_lang('system', 'seconds_ago'), intval($d));intval($d); }

	return psm_get_lang('system', 'a_second_ago');
}

/**
 * Get a localised date from MySQL date format
 * @param string $time
 * @return string
 */
function psm_date($time) {
	if(empty($time) || $time == '0000-00-00 00:00:00') {
		return psm_get_lang('system', 'never');
	}
	return strftime('%x %X', strtotime($time));
}

/**
 * Check if an update is available for PHP Server Monitor.
 *
 * Will only check for new version if user turned updates on in config.
 * @return boolean
 */
function psm_update_available() {
	if(!psm_get_conf('show_update')) {
		// user does not want updates, fair enough.
		return false;
	}

	$last_update = psm_get_conf('last_update_check');

	if((time() - PSM_UPDATE_INTERVAL) > $last_update) {
		// been more than a week since update, lets go
		// update last check date
		psm_update_conf('last_update_check', time());
		$latest = psm_curl_get(PSM_UPDATE_URL);
		// add latest version to database
		if($latest !== false && strlen($latest) < 15) {
			psm_update_conf('version_update_check', $latest);
		}
	} else {
		$latest = psm_get_conf('version_update_check');
	}

	if($latest != false) {
		$current = psm_get_conf('version');
		return version_compare($latest, $current, '>');
	} else {
		return false;
	}
}






/**
 * Try existence of a GET var, if not return the alternative
 * @param string $key
 * @param string $alt
 * @return mixed
 */
function psm_GET($key, $alt = null) {
	if(isset($_GET[$key])) {
		return $_GET[$key];
	} else {
		return $alt;
	}
}

/**
 * Try existence of a POST var, if not return the alternative
 * @param string $key
 * @param string $alt
 * @return mixed
 */
function psm_POST($key, $alt = null) {
	if(isset($_POST[$key])) {
		return $_POST[$key];
	} else {
		return $alt;
	}
}

/**
 * Check if we are in CLI mode
 *
 * Note, php_sapi cannot be used because cgi-fcgi returns both for web and cli.
 * @return boolean
 */
function psm_is_cli() {
	return (!isset($_SERVER['SERVER_SOFTWARE']) || php_sapi_name() == 'cli');
}

###############################################
#
# Debug functions
#
###############################################

/**
 * Only used for debugging and testing
 *
 * @param mixed $arr
 */
function pre($arr = null) {
	echo "<pre>";
	if ($arr === null) {
		debug_print_backtrace();
	}
	print_r($arr);
	echo "</pre>";
}

/**
 * Send headers to the browser to avoid caching
 */
function psm_no_cache() {
	header("Expires: Mon, 20 Dec 1998 01:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
}

/**
 * Encrypts the password for storage in the database
 *
 * @param string $key
 * @param string $password
 * @return string
 * @author Pavel Laupe Dvorak <pavel@pavel-dvorak.cz>
 */
function psm_password_encrypt($key, $password)
{
    if(empty($password))
        return '';

    if (empty($key))
        throw new \InvalidArgumentException('invalid_encryption_key');

    $iv = mcrypt_create_iv(
		mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC),
		MCRYPT_DEV_URANDOM
	);

	$encrypted = base64_encode(
		$iv .
		mcrypt_encrypt(
			MCRYPT_RIJNDAEL_128,
			hash('sha256',  $key, true),
			$password,
			MCRYPT_MODE_CBC,
			$iv
		)
	);

	return $encrypted;
}

/**
 * Decrypts password stored in the database for future use
 *
 * @param string $key
 * @param string $encryptedString
 * @return string
 * @author Pavel Laupe Dvorak <pavel@pavel-dvorak.cz>
 */
function psm_password_decrypt($key, $encryptedString)
{
	if(empty($encryptedString))
		return '';

	if (empty($key))
         throw new \InvalidArgumentException('invalid_encryption_key');
	
	$data = base64_decode($encryptedString);
	$iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));

	$decrypted = rtrim(
		mcrypt_decrypt(
			MCRYPT_RIJNDAEL_128,
			hash('sha256',  $key, true),
			substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)),
			MCRYPT_MODE_CBC,
			$iv
		),
		"\0"
	);

	return $decrypted;
}


    /**
     * Hash the password using the specified algorithm
     *
     * @param string $password The password to hash
     * @param int    $algo     The algorithm to use (Defined by PASSWORD_* constants)
     * @param array  $options  The options for the algorithm to use
     *
     * @return string|false The hashed password, or false on error.
     */
    function password_hash($password, $algo, array $options = array()) {
        if (!function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
            return null;
        }
        if (!is_string($password)) {
            trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
            return null;
        }
        if (!is_int($algo)) {
            trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
            return null;
        }
        switch ($algo) {
            case PASSWORD_BCRYPT:
                // Note that this is a C constant, but not exposed to PHP, so we don't define it here.
                $cost = 10;
                if (isset($options['cost'])) {
                    $cost = $options['cost'];
                    if ($cost < 4 || $cost > 31) {
                        trigger_error(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), E_USER_WARNING);
                        return null;
                    }
                }
                // The length of salt to generate
                $raw_salt_len = 16;
                // The length required in the final serialization
                $required_salt_len = 22;
                $hash_format = sprintf("$2y$%02d$", $cost);
                break;
            default:
                trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
                return null;
        }
        if (isset($options['salt'])) {
            switch (gettype($options['salt'])) {
                case 'NULL':
                case 'boolean':
                case 'integer':
                case 'double':
                case 'string':
                    $salt = (string) $options['salt'];
                    break;
                case 'object':
                    if (method_exists($options['salt'], '__tostring')) {
                        $salt = (string) $options['salt'];
                        break;
                    }
                case 'array':
                case 'resource':
                default:
                    trigger_error('password_hash(): Non-string salt parameter supplied', E_USER_WARNING);
                    return null;
            }
            if (strlen($salt) < $required_salt_len) {
                trigger_error(sprintf("password_hash(): Provided salt is too short: %d expecting %d", strlen($salt), $required_salt_len), E_USER_WARNING);
                return null;
            } elseif (0 == preg_match('#^[a-zA-Z0-9./]+$#D', $salt)) {
                $salt = str_replace('+', '.', base64_encode($salt));
            }
        } else {
            $buffer = '';
            $buffer_valid = false;
            if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
                $buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $buffer = openssl_random_pseudo_bytes($raw_salt_len);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid && is_readable('/dev/urandom')) {
                $f = fopen('/dev/urandom', 'r');
                $read = strlen($buffer);
                while ($read < $raw_salt_len) {
                    $buffer .= fread($f, $raw_salt_len - $read);
                    $read = strlen($buffer);
                }
                fclose($f);
                if ($read >= $raw_salt_len) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid || strlen($buffer) < $raw_salt_len) {
                $bl = strlen($buffer);
                for ($i = 0; $i < $raw_salt_len; $i++) {
                    if ($i < $bl) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }
            $salt = str_replace('+', '.', base64_encode($buffer));
        }
        $salt = substr($salt, 0, $required_salt_len);
        $hash = $hash_format . $salt;
        $ret = crypt($password, $hash);
        if (!is_string($ret) || strlen($ret) <= 13) {
            return false;
        }
        return $ret;
    }
