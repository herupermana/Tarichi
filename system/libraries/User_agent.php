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
 * User Agent Class.
 *
 * Identifies the platform, browser, robot, or mobile devise of the browsing agent
 *
 * @category	User Agent
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/user_agent.html
 */
class CI_User_agent
{
    public $agent = null;

    public $is_browser = false;
    public $is_robot = false;
    public $is_mobile = false;

    public $languages = [];
    public $charsets = [];

    public $platforms = [];
    public $browsers = [];
    public $mobiles = [];
    public $robots = [];

    public $platform = '';
    public $browser = '';
    public $version = '';
    public $mobile = '';
    public $robot = '';

    /**
     * Constructor.
     *
     * Sets the User Agent and runs the compilation routine
     *
     * @return void
     */
    public function __construct()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->agent = trim($_SERVER['HTTP_USER_AGENT']);
        }

        if (!is_null($this->agent)) {
            if ($this->_load_agent_file()) {
                $this->_compile_data();
            }
        }

        log_message('debug', 'User Agent Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Compile the User Agent Data.
     *
     * @return bool
     */
    private function _load_agent_file()
    {
        if (!@include(APPPATH.'config/user_agents'.EXT)) {
            return false;
        }

        $return = false;

        if (isset($platforms)) {
            $this->platforms = $platforms;
            unset($platforms);
            $return = true;
        }

        if (isset($browsers)) {
            $this->browsers = $browsers;
            unset($browsers);
            $return = true;
        }

        if (isset($mobiles)) {
            $this->mobiles = $mobiles;
            unset($mobiles);
            $return = true;
        }

        if (isset($robots)) {
            $this->robots = $robots;
            unset($robots);
            $return = true;
        }

        return $return;
    }

    // --------------------------------------------------------------------

    /**
     * Compile the User Agent Data.
     *
     * @return bool
     */
    private function _compile_data()
    {
        $this->_set_platform();

        foreach (['_set_browser', '_set_robot', '_set_mobile'] as $function) {
            if ($this->$function() === true) {
                break;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set the Platform.
     *
     * @return mixed
     */
    private function _set_platform()
    {
        if (is_array($this->platforms) and count($this->platforms) > 0) {
            foreach ($this->platforms as $key => $val) {
                if (preg_match('|'.preg_quote($key).'|i', $this->agent)) {
                    $this->platform = $val;

                    return true;
                }
            }
        }
        $this->platform = 'Unknown Platform';
    }

    // --------------------------------------------------------------------

    /**
     * Set the Browser.
     *
     * @return bool
     */
    private function _set_browser()
    {
        if (is_array($this->browsers) and count($this->browsers) > 0) {
            foreach ($this->browsers as $key => $val) {
                if (preg_match('|'.preg_quote($key).".*?([0-9\.]+)|i", $this->agent, $match)) {
                    $this->is_browser = true;
                    $this->version = $match[1];
                    $this->browser = $val;
                    $this->_set_mobile();

                    return true;
                }
            }
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Set the Robot.
     *
     * @return bool
     */
    private function _set_robot()
    {
        if (is_array($this->robots) and count($this->robots) > 0) {
            foreach ($this->robots as $key => $val) {
                if (preg_match('|'.preg_quote($key).'|i', $this->agent)) {
                    $this->is_robot = true;
                    $this->robot = $val;

                    return true;
                }
            }
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Set the Mobile Device.
     *
     * @return bool
     */
    private function _set_mobile()
    {
        if (is_array($this->mobiles) and count($this->mobiles) > 0) {
            foreach ($this->mobiles as $key => $val) {
                if (false !== (strpos(strtolower($this->agent), $key))) {
                    $this->is_mobile = true;
                    $this->mobile = $val;

                    return true;
                }
            }
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Set the accepted languages.
     *
     * @return void
     */
    private function _set_languages()
    {
        if ((count($this->languages) == 0) and isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) and $_SERVER['HTTP_ACCEPT_LANGUAGE'] != '') {
            $languages = preg_replace('/(;q=[0-9\.]+)/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));

            $this->languages = explode(',', $languages);
        }

        if (count($this->languages) == 0) {
            $this->languages = ['Undefined'];
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set the accepted character sets.
     *
     * @return void
     */
    private function _set_charsets()
    {
        if ((count($this->charsets) == 0) and isset($_SERVER['HTTP_ACCEPT_CHARSET']) and $_SERVER['HTTP_ACCEPT_CHARSET'] != '') {
            $charsets = preg_replace('/(;q=.+)/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])));

            $this->charsets = explode(',', $charsets);
        }

        if (count($this->charsets) == 0) {
            $this->charsets = ['Undefined'];
        }
    }

    // --------------------------------------------------------------------

    /**
     * Is Browser.
     *
     * @return bool
     */
    public function is_browser($key = null)
    {
        if (!$this->is_browser) {
            return false;
        }

        // No need to be specific, it's a browser
        if ($key === null) {
            return true;
        }

        // Check for a specific browser
        return array_key_exists($key, $this->browsers) and $this->browser === $this->browsers[$key];
    }

    // --------------------------------------------------------------------

    /**
     * Is Robot.
     *
     * @return bool
     */
    public function is_robot($key = null)
    {
        if (!$this->is_robot) {
            return false;
        }

        // No need to be specific, it's a robot
        if ($key === null) {
            return true;
        }

        // Check for a specific robot
        return array_key_exists($key, $this->robots) and $this->robot === $this->robots[$key];
    }

    // --------------------------------------------------------------------

    /**
     * Is Mobile.
     *
     * @return bool
     */
    public function is_mobile($key = null)
    {
        if (!$this->is_mobile) {
            return false;
        }

        // No need to be specific, it's a mobile
        if ($key === null) {
            return true;
        }

        // Check for a specific robot
        return array_key_exists($key, $this->mobiles) and $this->mobile === $this->mobiles[$key];
    }

    // --------------------------------------------------------------------

    /**
     * Is this a referral from another site?
     *
     * @return bool
     */
    public function is_referral()
    {
        if (!isset($_SERVER['HTTP_REFERER']) or $_SERVER['HTTP_REFERER'] == '') {
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Agent String.
     *
     * @return string
     */
    public function agent_string()
    {
        return $this->agent;
    }

    // --------------------------------------------------------------------

    /**
     * Get Platform.
     *
     * @return string
     */
    public function platform()
    {
        return $this->platform;
    }

    // --------------------------------------------------------------------

    /**
     * Get Browser Name.
     *
     * @return string
     */
    public function browser()
    {
        return $this->browser;
    }

    // --------------------------------------------------------------------

    /**
     * Get the Browser Version.
     *
     * @return string
     */
    public function version()
    {
        return $this->version;
    }

    // --------------------------------------------------------------------

    /**
     * Get The Robot Name.
     *
     * @return string
     */
    public function robot()
    {
        return $this->robot;
    }
    // --------------------------------------------------------------------

    /**
     * Get the Mobile Device.
     *
     * @return string
     */
    public function mobile()
    {
        return $this->mobile;
    }

    // --------------------------------------------------------------------

    /**
     * Get the referrer.
     *
     * @return bool
     */
    public function referrer()
    {
        return (!isset($_SERVER['HTTP_REFERER']) or $_SERVER['HTTP_REFERER'] == '') ? '' : trim($_SERVER['HTTP_REFERER']);
    }

    // --------------------------------------------------------------------

    /**
     * Get the accepted languages.
     *
     * @return array
     */
    public function languages()
    {
        if (count($this->languages) == 0) {
            $this->_set_languages();
        }

        return $this->languages;
    }

    // --------------------------------------------------------------------

    /**
     * Get the accepted Character Sets.
     *
     * @return array
     */
    public function charsets()
    {
        if (count($this->charsets) == 0) {
            $this->_set_charsets();
        }

        return $this->charsets;
    }

    // --------------------------------------------------------------------

    /**
     * Test for a particular language.
     *
     * @return bool
     */
    public function accept_lang($lang = 'en')
    {
        return in_array(strtolower($lang), $this->languages(), true);
    }

    // --------------------------------------------------------------------

    /**
     * Test for a particular character set.
     *
     * @return bool
     */
    public function accept_charset($charset = 'utf-8')
    {
        return in_array(strtolower($charset), $this->charsets(), true);
    }
}

/* End of file User_agent.php */
/* Location: ./system/libraries/User_agent.php */
