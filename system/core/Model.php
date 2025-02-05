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
 * CodeIgniter Model Class.
 *
 * @category	Libraries
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Model
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        log_message('debug', 'Model Class Initialized');
    }

    /**
     * __get.
     *
     * Allows models to access CI's loaded classes using the same
     * syntax as controllers.
     */
    public function __get($key)
    {
        $CI = &get_instance();

        return $CI->$key;
    }
}
// END Model Class

/* End of file Model.php */
/* Location: ./system/core/Model.php */
