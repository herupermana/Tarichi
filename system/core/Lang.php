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
 * Language Class.
 *
 * @category	Language
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/language.html
 */
class CI_Lang
{
    public $language = [];
    public $is_loaded = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        log_message('debug', 'Language Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Load a language file.
     *
     * @param	mixed	the name of the language file to be loaded. Can be an array
     * @param	string	the language (english, etc.)
     *
     * @return mixed
     */
    public function load($langfile = '', $idiom = '', $return = false, $add_suffix = true, $alt_path = '')
    {
        $langfile = str_replace(EXT, '', $langfile);

        if ($add_suffix == true) {
            $langfile = str_replace('_lang.', '', $langfile).'_lang';
        }

        $langfile .= EXT;

        if (in_array($langfile, $this->is_loaded, true)) {
            return;
        }

        $config = &get_config();

        if ($idiom == '') {
            $deft_lang = (!isset($config['language'])) ? 'english' : $config['language'];
            $idiom = ($deft_lang == '') ? 'english' : $deft_lang;
        }

        // Determine where the language file is and load it
        if ($alt_path != '' && file_exists($alt_path.'language/'.$idiom.'/'.$langfile)) {
            include $alt_path.'language/'.$idiom.'/'.$langfile;
        } else {
            $found = false;

            foreach (get_instance()->load->get_package_paths(true) as $package_path) {
                if (file_exists($package_path.'language/'.$idiom.'/'.$langfile)) {
                    include $package_path.'language/'.$idiom.'/'.$langfile;
                    $found = true;
                    break;
                }
            }

            if ($found !== true) {
                show_error('Unable to load the requested language file: language/'.$idiom.'/'.$langfile);
            }
        }

        if (!isset($lang)) {
            log_message('error', 'Language file contains no data: language/'.$idiom.'/'.$langfile);

            return;
        }

        if ($return == true) {
            return $lang;
        }

        $this->is_loaded[] = $langfile;
        $this->language = array_merge($this->language, $lang);
        unset($lang);

        log_message('debug', 'Language file loaded: language/'.$idiom.'/'.$langfile);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a single line of text from the language array.
     *
     * @param string $line the language line
     *
     * @return string
     */
    public function line($line = '')
    {
        $line = ($line == '' or !isset($this->language[$line])) ? false : $this->language[$line];

        return $line;
    }
}
// END Language Class

/* End of file Lang.php */
/* Location: ./system/core/Lang.php */
