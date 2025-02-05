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
 * CodeIgniter Array Helpers.
 *
 * @category	Helpers
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/helpers/array_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Element.
 *
 * Lets you determine whether an array index is set and whether it has a value.
 * If the element is empty it returns FALSE (or whatever you specify as the default value.)
 *
 * @param	string
 * @param	array
 * @param	mixed
 *
 * @return mixed depends on what the array contains
 */
if (!function_exists('element')) {
    function element($item, $array, $default = false)
    {
        if (!isset($array[$item]) or $array[$item] == '') {
            return $default;
        }

        return $array[$item];
    }
}

// ------------------------------------------------------------------------

/**
 * Random Element - Takes an array as input and returns a random element.
 *
 * @param	array
 *
 * @return mixed depends on what the array contains
 */
if (!function_exists('random_element')) {
    function random_element($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        return $array[array_rand($array)];
    }
}

// --------------------------------------------------------------------

/**
 * Elements.
 *
 * Returns only the array items specified.  Will return a default value if
 * it is not set.
 *
 * @param	array
 * @param	array
 * @param	mixed
 *
 * @return mixed depends on what the array contains
 */
if (!function_exists('elements')) {
    function elements($items, $array, $default = false)
    {
        $return = [];

        if (!is_array($items)) {
            $items = [$items];
        }

        foreach ($items as $item) {
            if (isset($array[$item])) {
                $return[$item] = $array[$item];
            } else {
                $return[$item] = $default;
            }
        }

        return $return;
    }
}

/* End of file array_helper.php */
/* Location: ./system/helpers/array_helper.php */
