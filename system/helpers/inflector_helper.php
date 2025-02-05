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
 * CodeIgniter Inflector Helpers.
 *
 * @category	Helpers
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/helpers/directory_helper.html
 */

// --------------------------------------------------------------------

/**
 * Singular.
 *
 * Takes a plural word and makes it singular
 *
 * @param	string
 *
 * @return string
 */
if (!function_exists('singular')) {
    function singular($str)
    {
        $str = strtolower(trim($str));
        $end = substr($str, -3);

        if ($end == 'ies') {
            $str = substr($str, 0, strlen($str) - 3).'y';
        } elseif ($end == 'ses') {
            $str = substr($str, 0, strlen($str) - 2);
        } else {
            $end = substr($str, -1);

            if ($end == 's') {
                $str = substr($str, 0, strlen($str) - 1);
            }
        }

        return $str;
    }
}

// --------------------------------------------------------------------

/**
 * Plural.
 *
 * Takes a singular word and makes it plural
 *
 * @param	string
 * @param	bool
 *
 * @return string
 */
if (!function_exists('plural')) {
    function plural($str, $force = false)
    {
        $str = strtolower(trim($str));
        $end = substr($str, -1);

        if ($end == 'y') {
            // Y preceded by vowel => regular plural
            $vowels = ['a', 'e', 'i', 'o', 'u'];
            $str = in_array(substr($str, -2, 1), $vowels) ? $str.'s' : substr($str, 0, -1).'ies';
        } elseif ($end == 'h') {
            if (substr($str, -2) == 'ch' or substr($str, -2) == 'sh') {
                $str .= 'es';
            } else {
                $str .= 's';
            }
        } elseif ($end == 's') {
            if ($force == true) {
                $str .= 'es';
            }
        } else {
            $str .= 's';
        }

        return $str;
    }
}

// --------------------------------------------------------------------

/**
 * Camelize.
 *
 * Takes multiple words separated by spaces or underscores and camelizes them
 *
 * @param	string
 *
 * @return string
 */
if (!function_exists('camelize')) {
    function camelize($str)
    {
        $str = 'x'.strtolower(trim($str));
        $str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

        return substr(str_replace(' ', '', $str), 1);
    }
}

// --------------------------------------------------------------------

/**
 * Underscore.
 *
 * Takes multiple words separated by spaces and underscores them
 *
 * @param	string
 *
 * @return string
 */
if (!function_exists('underscore')) {
    function underscore($str)
    {
        return preg_replace('/[\s]+/', '_', strtolower(trim($str)));
    }
}

// --------------------------------------------------------------------

/**
 * Humanize.
 *
 * Takes multiple words separated by underscores and changes them to spaces
 *
 * @param	string
 *
 * @return string
 */
if (!function_exists('humanize')) {
    function humanize($str)
    {
        return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
    }
}

/* End of file inflector_helper.php */
/* Location: ./system/helpers/inflector_helper.php */
