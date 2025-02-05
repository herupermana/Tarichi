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
 * CodeIgniter Hooks Class.
 *
 * Provides a mechanism to extend the base system without hacking.
 *
 * @category	Libraries
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/encryption.html
 */
class CI_Hooks
{
    public $enabled = false;
    public $hooks = [];
    public $in_progress = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_initialize();
        log_message('debug', 'Hooks Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Initialize the Hooks Preferences.
     *
     * @return void
     */
    public function _initialize()
    {
        $CFG = &load_class('Config', 'core');

        // If hooks are not enabled in the config file
        // there is nothing else to do

        if ($CFG->item('enable_hooks') == false) {
            return;
        }

        // Grab the "hooks" definition file.
        // If there are no hooks, we're done.

        @include APPPATH.'config/hooks'.EXT;

        if (!isset($hook) or !is_array($hook)) {
            return;
        }

        $this->hooks = &$hook;
        $this->enabled = true;
    }

    // --------------------------------------------------------------------

    /**
     * Call Hook.
     *
     * Calls a particular hook
     *
     * @param	string	the hook name
     *
     * @return mixed
     */
    public function _call_hook($which = '')
    {
        if (!$this->enabled or !isset($this->hooks[$which])) {
            return false;
        }

        if (isset($this->hooks[$which][0]) and is_array($this->hooks[$which][0])) {
            foreach ($this->hooks[$which] as $val) {
                $this->_run_hook($val);
            }
        } else {
            $this->_run_hook($this->hooks[$which]);
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Run Hook.
     *
     * Runs a particular hook
     *
     * @param	array	the hook details
     *
     * @return bool
     */
    public function _run_hook($data)
    {
        if (!is_array($data)) {
            return false;
        }

        // -----------------------------------
        // Safety - Prevents run-away loops
        // -----------------------------------

        // If the script being called happens to have the same
        // hook call within it a loop can happen

        if ($this->in_progress == true) {
            return;
        }

        // -----------------------------------
        // Set file path
        // -----------------------------------

        if (!isset($data['filepath']) or !isset($data['filename'])) {
            return false;
        }

        $filepath = APPPATH.$data['filepath'].'/'.$data['filename'];

        if (!file_exists($filepath)) {
            return false;
        }

        // -----------------------------------
        // Set class/function name
        // -----------------------------------

        $class = false;
        $function = false;
        $params = '';

        if (isset($data['class']) and $data['class'] != '') {
            $class = $data['class'];
        }

        if (isset($data['function'])) {
            $function = $data['function'];
        }

        if (isset($data['params'])) {
            $params = $data['params'];
        }

        if ($class === false and $function === false) {
            return false;
        }

        // -----------------------------------
        // Set the in_progress flag
        // -----------------------------------

        $this->in_progress = true;

        // -----------------------------------
        // Call the requested class and/or function
        // -----------------------------------

        if ($class !== false) {
            if (!class_exists($class)) {
                require $filepath;
            }

            $HOOK = new $class();
            $HOOK->$function($params);
        } else {
            if (!function_exists($function)) {
                require $filepath;
            }

            $function($params);
        }

        $this->in_progress = false;

        return true;
    }
}

// END CI_Hooks class

/* End of file Hooks.php */
/* Location: ./system/core/Hooks.php */
