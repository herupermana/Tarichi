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
 * CodeIgniter String Helpers.
 *
 * @category	Helpers
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/helpers/string_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Trim Slashes.
 *
 * Removes any leading/trailing slashes from a string:
 *
 * /this/that/theother/
 *
 * becomes:
 *
 * this/that/theother
 *
 * @param	string
 *
 * @return string
 */
if (!function_exists('trim_slashes')) {
    function trim_slashes($str)
    {
        return trim($str, '/');
    }
}

// ------------------------------------------------------------------------

/**
 * Strip Slashes.
 *
 * Removes slashes contained in a string or in an array
 *
 * @param	mixed	string or array
 *
 * @return mixed string or array
 */
if (!function_exists('strip_slashes')) {
    function strip_slashes($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = strip_slashes($val);
            }
        } else {
            $str = stripslashes($str);
        }

        return $str;
    }
}

// ------------------------------------------------------------------------

/**
 * Strip Quotes.
 *
 * Removes single and double quotes from a string
 *
 * @param	string
 *
 * @return string
 */
if (!function_exists('strip_quotes')) {
    function strip_quotes($str)
    {
        return str_replace(['"', "'"], '', $str);
    }
}

// ------------------------------------------------------------------------

/**
 * Quotes to Entities.
 *
 * Converts single and double quotes to entities
 *
 * @param	string
 *
 * @return string
 */
if (!function_exists('quotes_to_entities')) {
    function quotes_to_entities($str)
    {
        return str_replace(["\'", '"', "'", '"'], ['&#39;', '&quot;', '&#39;', '&quot;'], $str);
    }
}

// ------------------------------------------------------------------------

/**
 * Reduce Double Slashes.
 *
 * Converts double slashes in a string to a single slash,
 * except those found in http://
 *
 * http://www.some-site.com//index.php
 *
 * becomes:
 *
 * http://www.some-site.com/index.php
 *
 * @param	string
 *
 * @return string
 */
if (!function_exists('reduce_double_slashes')) {
    function reduce_double_slashes($str)
    {
        return preg_replace('#(^|[^:])//+#', '\\1/', $str);
    }
}

// ------------------------------------------------------------------------

/**
 * Reduce Multiples.
 *
 * Reduces multiple instances of a particular character.  Example:
 *
 * Fred, Bill,, Joe, Jimmy
 *
 * becomes:
 *
 * Fred, Bill, Joe, Jimmy
 *
 * @param	string
 * @param	string	the character you wish to reduce
 * @param	bool	TRUE/FALSE - whether to trim the character from the beginning/end
 *
 * @return string
 */
if (!function_exists('reduce_multiples')) {
    function reduce_multiples($str, $character = ',', $trim = false)
    {
        $str = preg_replace('#'.preg_quote($character, '#').'{2,}#', $character, $str);

        if ($trim === true) {
            $str = trim($str, $character);
        }

        return $str;
    }
}

// ------------------------------------------------------------------------

/**
 * Create a Random String.
 *
 * Useful for generating passwords or hashes.
 *
 * @param	string	type of random string.  basic, alpha, alunum, numeric, nozero, unique, md5, encrypt and sha1
 * @param	int	number of characters
 *
 * @return string
 */
if (!function_exists('random_string')) {
    function random_string($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'basic': return mt_rand();
                break;
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':

                    switch ($type) {
                        case 'alpha':	$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            break;
                        case 'alnum':	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            break;
                        case 'numeric':	$pool = '0123456789';
                            break;
                        case 'nozero':	$pool = '123456789';
                            break;
                    }

                    $str = '';
                    for ($i = 0; $i < $len; $i++) {
                        $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
                    }

                    return $str;
                break;
            case 'unique':
            case 'md5':

                        return md5(uniqid(mt_rand()));
                break;
            case 'encrypt':
            case 'sha1':

                        $CI = &get_instance();
                        $CI->load->helper('security');

                        return do_hash(uniqid(mt_rand(), true), 'sha1');
                break;
        }
    }
}

// ------------------------------------------------------------------------

/**
 * Alternator.
 *
 * Allows strings to be alternated.  See docs...
 *
 * @param	string (as many parameters as needed)
 *
 * @return string
 */
if (!function_exists('alternator')) {
    function alternator()
    {
        static $i;

        if (func_num_args() == 0) {
            $i = 0;

            return '';
        }
        $args = func_get_args();

        return $args[($i++ % count($args))];
    }
}

// ------------------------------------------------------------------------

/**
 * Repeater function.
 *
 * @param	string
 * @param	int	number of repeats
 *
 * @return string
 */
if (!function_exists('repeater')) {
    function repeater($data, $num = 1)
    {
        return ($num > 0) ? str_repeat($data, $num) : '';
    }
}

/* End of file string_helper.php */
/* Location: ./system/helpers/string_helper.php */
