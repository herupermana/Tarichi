<?php

 if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * CodeIgniter.
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 *
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Input Class.
 *
 * Pre-processes global input data for security
 *
 * @category	Input
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/input.html
 */
class CI_Input
{
    public $ip_address = false;
    public $user_agent = false;
    public $_allow_get_array = true;
    public $_standardize_newlines = true;
    public $_enable_xss = false; // Set automatically based on config setting
    public $_enable_csrf = false; // Set automatically based on config setting

    protected $headers = [];

    /**
     * Constructor.
     *
     * Sets whether to globally enable the XSS processing
     * and whether to allow the $_GET array
     */
    public function __construct()
    {
        log_message('debug', 'Input Class Initialized');

        $this->_allow_get_array = (config_item('allow_get_array') === true);
        $this->_enable_xss = (config_item('global_xss_filtering') === true);
        $this->_enable_csrf = (config_item('csrf_protection') === true);

        // Do we need to load the security class?
        if ($this->_enable_xss == true or $this->_enable_csrf == true) {
            $this->security = &load_class('Security');
        }

        // Do we need the UTF-8 class?
        if (UTF8_ENABLED === true) {
            global $UNI;
            $this->uni = &$UNI;
        }

        // Sanitize global arrays
        $this->_sanitize_globals();
    }

    // --------------------------------------------------------------------

    /**
     * Fetch from array.
     *
     * This is a helper function to retrieve values from global arrays
     *
     * @param	array
     * @param	string
     * @param	bool
     *
     * @return string
     */
    public function _fetch_from_array(&$array, $index = '', $xss_clean = false)
    {
        if (!isset($array[$index])) {
            return false;
        }

        if ($xss_clean === true) {
            $_security = &load_class('Security');

            return $_security->xss_clean($array[$index]);
        }

        return $array[$index];
    }

    // --------------------------------------------------------------------

    /**
     * Fetch an item from the GET array.
     *
     * @param	string
     * @param	bool
     *
     * @return string
     */
    public function get($index = null, $xss_clean = false)
    {
        // Check if a field has been provided
        if ($index === null and !empty($_GET)) {
            $get = [];

            // loop through the full _GET array
            foreach (array_keys($_GET) as $key) {
                $get[$key] = $this->_fetch_from_array($_GET, $key, $xss_clean);
            }

            return $get;
        }

        return $this->_fetch_from_array($_GET, $index, $xss_clean);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch an item from the POST array.
     *
     * @param	string
     * @param	bool
     *
     * @return string
     */
    public function post($index = null, $xss_clean = false)
    {
        // Check if a field has been provided
        if ($index === null and !empty($_POST)) {
            $post = [];

            // Loop through the full _POST array and return it
            foreach (array_keys($_POST) as $key) {
                $post[$key] = $this->_fetch_from_array($_POST, $key, $xss_clean);
            }

            return $post;
        }

        return $this->_fetch_from_array($_POST, $index, $xss_clean);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch an item from either the GET array or the POST.
     *
     * @param	string	The index key
     * @param	bool	XSS cleaning
     *
     * @return string
     */
    public function get_post($index = '', $xss_clean = false)
    {
        if (!isset($_POST[$index])) {
            return $this->get($index, $xss_clean);
        } else {
            return $this->post($index, $xss_clean);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Fetch an item from the COOKIE array.
     *
     * @param	string
     * @param	bool
     *
     * @return string
     */
    public function cookie($index = '', $xss_clean = false)
    {
        return $this->_fetch_from_array($_COOKIE, $index, $xss_clean);
    }

    // ------------------------------------------------------------------------

    /**
     * Set cookie.
     *
     * Accepts six parameter, or you can submit an associative
     * array in the first parameter containing all the values.
     *
     * @param	mixed
     * @param	string	the value of the cookie
     * @param	string	the number of seconds until expiration
     * @param	string	the cookie domain.  Usually:  .yourdomain.com
     * @param	string	the cookie path
     * @param	string	the cookie prefix
     * @param	bool	true makes the cookie secure
     *
     * @return void
     */
    public function set_cookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = false)
    {
        if (is_array($name)) {
            // always leave 'name' in last place, as the loop will break otherwise, due to $$item
            foreach (['value', 'expire', 'domain', 'path', 'prefix', 'secure', 'name'] as $item) {
                if (isset($name[$item])) {
                    $$item = $name[$item];
                }
            }
        }

        if ($prefix == '' and config_item('cookie_prefix') != '') {
            $prefix = config_item('cookie_prefix');
        }
        if ($domain == '' and config_item('cookie_domain') != '') {
            $domain = config_item('cookie_domain');
        }
        if ($path == '/' and config_item('cookie_path') != '/') {
            $path = config_item('cookie_path');
        }
        if ($secure == false and config_item('cookie_secure') != false) {
            $secure = config_item('cookie_secure');
        }

        if (!is_numeric($expire)) {
            $expire = time() - 86500;
        } else {
            $expire = ($expire > 0) ? time() + $expire : 0;
        }

        setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch an item from the SERVER array.
     *
     * @param	string
     * @param	bool
     *
     * @return string
     */
    public function server($index = '', $xss_clean = false)
    {
        return $this->_fetch_from_array($_SERVER, $index, $xss_clean);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the IP Address.
     *
     * @return string
     */
    public function ip_address()
    {
        if ($this->ip_address !== false) {
            return $this->ip_address;
        }

        if (config_item('proxy_ips') != '' && $this->server('HTTP_X_FORWARDED_FOR') && $this->server('REMOTE_ADDR')) {
            $proxies = preg_split('/[\s,]/', config_item('proxy_ips'), -1, PREG_SPLIT_NO_EMPTY);
            $proxies = is_array($proxies) ? $proxies : [$proxies];

            $this->ip_address = in_array($_SERVER['REMOTE_ADDR'], $proxies) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        } elseif ($this->server('REMOTE_ADDR') and $this->server('HTTP_CLIENT_IP')) {
            $this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($this->server('REMOTE_ADDR')) {
            $this->ip_address = $_SERVER['REMOTE_ADDR'];
        } elseif ($this->server('HTTP_CLIENT_IP')) {
            $this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($this->server('HTTP_X_FORWARDED_FOR')) {
            $this->ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if ($this->ip_address === false) {
            $this->ip_address = '0.0.0.0';

            return $this->ip_address;
        }

        if (strpos($this->ip_address, ',') !== false) {
            $x = explode(',', $this->ip_address);
            $this->ip_address = trim(end($x));
        }

        if (!$this->valid_ip($this->ip_address)) {
            $this->ip_address = '0.0.0.0';
        }

        return $this->ip_address;
    }

    // --------------------------------------------------------------------

    /**
     * Validate IP Address.
     *
     * Updated version suggested by Geert De Deckere
     *
     * @param	string
     *
     * @return string
     */
    public function valid_ip($ip)
    {
        $ip_segments = explode('.', $ip);

        // Always 4 segments needed
        if (count($ip_segments) != 4) {
            return false;
        }
        // IP can not start with 0
        if ($ip_segments[0][0] == '0') {
            return false;
        }
        // Check each segment
        foreach ($ip_segments as $segment) {
            // IP segments must be digits and can not be
            // longer than 3 digits or greater then 255
            if ($segment == '' or preg_match('/[^0-9]/', $segment) or $segment > 255 or strlen($segment) > 3) {
                return false;
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * User Agent.
     *
     * @return string
     */
    public function user_agent()
    {
        if ($this->user_agent !== false) {
            return $this->user_agent;
        }

        $this->user_agent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? false : $_SERVER['HTTP_USER_AGENT'];

        return $this->user_agent;
    }

    // --------------------------------------------------------------------

    /**
     * Sanitize Globals.
     *
     * This function does the following:
     *
     * Unsets $_GET data (if query strings are not enabled)
     *
     * Unsets all globals if register_globals is enabled
     *
     * Standardizes newline characters to \n
     *
     * @return void
     */
    public function _sanitize_globals()
    {
        // It would be "wrong" to unset any of these GLOBALS.
        $protected = ['_SERVER', '_GET', '_POST', '_FILES', '_REQUEST',
            '_SESSION', '_ENV', 'GLOBALS', 'HTTP_RAW_POST_DATA',
            'system_folder', 'application_folder', 'BM', 'EXT',
            'CFG', 'URI', 'RTR', 'OUT', 'IN', ];

        // Unset globals for securiy.
        // This is effectively the same as register_globals = off
        foreach ([$_GET, $_POST, $_COOKIE] as $global) {
            if (!is_array($global)) {
                if (!in_array($global, $protected)) {
                    global $$global;
                    $$global = null;
                }
            } else {
                foreach ($global as $key => $val) {
                    if (!in_array($key, $protected)) {
                        global $$key;
                        $$key = null;
                    }
                }
            }
        }

        // Is $_GET data allowed? If not we'll set the $_GET to an empty array
        if ($this->_allow_get_array == false) {
            $_GET = [];
        } else {
            if (is_array($_GET) and count($_GET) > 0) {
                foreach ($_GET as $key => $val) {
                    $_GET[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
                }
            }
        }

        // Clean $_POST Data
        if (is_array($_POST) and count($_POST) > 0) {
            foreach ($_POST as $key => $val) {
                $_POST[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
            }
        }

        // Clean $_COOKIE Data
        if (is_array($_COOKIE) and count($_COOKIE) > 0) {
            // Also get rid of specially treated cookies that might be set by a server
            // or silly application, that are of no use to a CI application anyway
            // but that when present will trip our 'Disallowed Key Characters' alarm
            // http://www.ietf.org/rfc/rfc2109.txt
            // note that the key names below are single quoted strings, and are not PHP variables
            unset($_COOKIE['$Version']);
            unset($_COOKIE['$Path']);
            unset($_COOKIE['$Domain']);

            foreach ($_COOKIE as $key => $val) {
                $_COOKIE[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
            }
        }

        // Sanitize PHP_SELF
        $_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);

        // CSRF Protection check
        if ($this->_enable_csrf == true) {
            $this->security->csrf_verify();
        }

        log_message('debug', 'Global POST and COOKIE data sanitized');
    }

    // --------------------------------------------------------------------

    /**
     * Clean Input Data.
     *
     * This is a helper function. It escapes data and
     * standardizes newline characters to \n
     *
     * @param	string
     *
     * @return string
     */
    public function _clean_input_data($str)
    {
        if (is_array($str)) {
            $new_array = [];
            foreach ($str as $key => $val) {
                $new_array[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
            }

            return $new_array;
        }

        // We strip slashes if magic quotes is on to keep things consistent
        if (function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc()) {
            $str = stripslashes($str);
        }

        // Clean UTF-8 if supported
        if (UTF8_ENABLED === true) {
            $str = $this->uni->clean_string($str);
        }

        // Should we filter the input data?
        if ($this->_enable_xss === true) {
            $str = $this->security->xss_clean($str);
        }

        // Standardize newlines if needed
        if ($this->_standardize_newlines == true) {
            if (strpos($str, "\r") !== false) {
                $str = str_replace(["\r\n", "\r"], PHP_EOL, $str);
            }
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Clean Keys.
     *
     * This is a helper function. To prevent malicious users
     * from trying to exploit keys we make sure that keys are
     * only named with alpha-numeric text and a few other items.
     *
     * @param	string
     *
     * @return string
     */
    public function _clean_input_keys($str)
    {
        if (!preg_match("/^[a-z0-9:_\/-]+$/i", $str)) {
            exit('Disallowed Key Characters.');
        }

        // Clean UTF-8 if supported
        if (UTF8_ENABLED === true) {
            $str = $this->uni->clean_string($str);
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Request Headers.
     *
     * In Apache, you can simply call apache_request_headers(), however for
     * people running other webservers the function is undefined.
     *
     * @return array
     */
    public function request_headers($xss_clean = false)
    {
        // Look at Apache go!
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers['Content-Type'] = (isset($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');

            foreach ($_SERVER as $key => $val) {
                if (strncmp($key, 'HTTP_', 5) === 0) {
                    $headers[substr($key, 5)] = $this->_fetch_from_array($_SERVER, $key, $xss_clean);
                }
            }
        }

        // take SOME_HEADER and turn it into Some-Header
        foreach ($headers as $key => $val) {
            $key = str_replace('_', ' ', strtolower($key));
            $key = str_replace(' ', '-', ucwords($key));

            $this->headers[$key] = $val;
        }

        return $this->headers;
    }

    // --------------------------------------------------------------------

    /**
     * Get Request Header.
     *
     * Returns the value of a single member of the headers class member
     *
     * @param 	string		array key for $this->headers
     * @param	bool		XSS Clean or not
     *
     * @return mixed FALSE on failure, string on success
     */
    public function get_request_header($index, $xss_clean = false)
    {
        if (empty($this->headers)) {
            $this->request_headers();
        }

        if (!isset($this->headers[$index])) {
            return false;
        }

        if ($xss_clean === true) {
            $_security = &load_class('Security');

            return $_security->xss_clean($this->headers[$index]);
        }

        return $this->headers[$index];
    }

    // --------------------------------------------------------------------

    /**
     * Is ajax Request?
     *
     * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
     *
     * @return bool
     */
    public function is_ajax_request()
    {
        return $this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    // --------------------------------------------------------------------

    /**
     * Is cli Request?
     *
     * Test to see if a request was made from the command line
     *
     * @return bool
     */
    public function is_cli_request()
    {
        return (bool) defined('STDIN');
    }
}
// END Input class

/* End of file Input.php */
/* Location: ./system/core/Input.php */
