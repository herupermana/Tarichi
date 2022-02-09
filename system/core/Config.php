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
 * CodeIgniter Config Class.
 *
 * This class contains functions that enable config files to be managed
 *
 * @category	Libraries
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Config
{
    public $config = [];
    public $is_loaded = [];
    public $_config_paths = [APPPATH];

    /**
     * Constructor.
     *
     * Sets the $config data from the primary config.php file as a class variable
     *
     * @param   string	the config file name
     * @param   bool  if configuration values should be loaded into their own section
     * @param   bool  true if errors should just return false, false if an error message should be displayed
     *
     * @return bool if the file was successfully loaded or not
     */
    public function __construct()
    {
        $this->config = &get_config();
        log_message('debug', 'Config Class Initialized');

        // Set the base_url automatically if none was provided
        if ($this->config['base_url'] == '') {
            if (isset($_SERVER['HTTP_HOST'])) {
                $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
                $base_url .= '://'.$_SERVER['HTTP_HOST'];
                $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
            } else {
                $base_url = 'http://localhost/';
            }

            $this->set_item('base_url', $base_url);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Load Config File.
     *
     * @param	string	the config file name
     * @param   bool  if configuration values should be loaded into their own section
     * @param   bool  true if errors should just return false, false if an error message should be displayed
     *
     * @return bool if the file was loaded correctly
     */
    public function load($file = '', $use_sections = false, $fail_gracefully = false)
    {
        $file = ($file == '') ? 'config' : str_replace(EXT, '', $file);
        $loaded = false;

        foreach ($this->_config_paths as $path) {
            $file_path = $path.'config/'.ENVIRONMENT.'/'.$file.EXT;

            if (in_array($file_path, $this->is_loaded, true)) {
                $loaded = true;
                continue;
            }

            if (!file_exists($file_path)) {
                log_message('debug', 'Config for '.ENVIRONMENT.' environment is not found. Trying global config.');
                $file_path = $path.'config/'.$file.EXT;

                if (!file_exists($file_path)) {
                    continue;
                }
            }

            include $file_path;

            if (!isset($config) or !is_array($config)) {
                if ($fail_gracefully === true) {
                    return false;
                }
                show_error('Your '.$file_path.' file does not appear to contain a valid configuration array.');
            }

            if ($use_sections === true) {
                if (isset($this->config[$file])) {
                    $this->config[$file] = array_merge($this->config[$file], $config);
                } else {
                    $this->config[$file] = $config;
                }
            } else {
                $this->config = array_merge($this->config, $config);
            }

            $this->is_loaded[] = $file_path;
            unset($config);

            $loaded = true;
            log_message('debug', 'Config file loaded: '.$file_path);
        }

        if ($loaded === false) {
            if ($fail_gracefully === true) {
                return false;
            }
            show_error('The configuration file '.ENVIRONMENT.'/'.$file.EXT.' and '.$file.EXT.' do not exist.');
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a config file item.
     *
     *
     * @param	string	the config item name
     * @param	string	the index name
     * @param	bool
     *
     * @return string
     */
    public function item($item, $index = '')
    {
        if ($index == '') {
            if (!isset($this->config[$item])) {
                return false;
            }

            $pref = $this->config[$item];
        } else {
            if (!isset($this->config[$index])) {
                return false;
            }

            if (!isset($this->config[$index][$item])) {
                return false;
            }

            $pref = $this->config[$index][$item];
        }

        return $pref;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a config file item - adds slash after item.
     *
     * The second parameter allows a slash to be added to the end of
     * the item, in the case of a path.
     *
     * @param	string	the config item name
     * @param	bool
     *
     * @return string
     */
    public function slash_item($item)
    {
        if (!isset($this->config[$item])) {
            return false;
        }

        return rtrim($this->config[$item], '/').'/';
    }

    // --------------------------------------------------------------------

    /**
     * Site URL.
     *
     * @param	string	the URI string
     *
     * @return string
     */
    public function site_url($uri = '')
    {
        if ($uri == '') {
            return $this->slash_item('base_url').$this->item('index_page');
        }

        if ($this->item('enable_query_strings') == false) {
            if (is_array($uri)) {
                $uri = implode('/', $uri);
            }

            $index = $this->item('index_page') == '' ? '' : $this->slash_item('index_page');
            $suffix = ($this->item('url_suffix') == false) ? '' : $this->item('url_suffix');

            return $this->slash_item('base_url').$index.trim($uri, '/').$suffix;
        } else {
            if (is_array($uri)) {
                $i = 0;
                $str = '';
                foreach ($uri as $key => $val) {
                    $prefix = ($i == 0) ? '' : '&';
                    $str .= $prefix.$key.'='.$val;
                    $i++;
                }

                $uri = $str;
            }

            return $this->slash_item('base_url').$this->item('index_page').'?'.$uri;
        }
    }

    // --------------------------------------------------------------------

    /**
     * System URL.
     *
     * @return string
     */
    public function system_url()
    {
        $x = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', BASEPATH));

        return $this->slash_item('base_url').end($x).'/';
    }

    // --------------------------------------------------------------------

    /**
     * Set a config file item.
     *
     * @param	string	the config item key
     * @param	string	the config item value
     *
     * @return void
     */
    public function set_item($item, $value)
    {
        $this->config[$item] = $value;
    }

    // --------------------------------------------------------------------

    /**
     * Assign to Config.
     *
     * This function is called by the front controller (CodeIgniter.php)
     * after the Config class is instantiated.  It permits config items
     * to be assigned or overriden by variables contained in the index.php file
     *
     * @param	array
     *
     * @return void
     */
    public function _assign_to_config($items = [])
    {
        if (is_array($items)) {
            foreach ($items as $key => $val) {
                $this->set_item($key, $val);
            }
        }
    }
}

// END CI_Config class

/* End of file Config.php */
/* Location: ./system/core/Config.php */
