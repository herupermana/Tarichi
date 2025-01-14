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
 * ODBC Result Class.
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_odbc_result extends CI_DB_result
{
    /**
     * Number of rows in the result set.
     *
     * @return int
     */
    public function num_rows()
    {
        return @odbc_num_rows($this->result_id);
    }

    // --------------------------------------------------------------------

    /**
     * Number of fields in the result set.
     *
     * @return int
     */
    public function num_fields()
    {
        return @odbc_num_fields($this->result_id);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch Field Names.
     *
     * Generates an array of column names
     *
     * @return array
     */
    public function list_fields()
    {
        $field_names = [];
        for ($i = 0; $i < $this->num_fields(); $i++) {
            $field_names[] = odbc_field_name($this->result_id, $i);
        }

        return $field_names;
    }

    // --------------------------------------------------------------------

    /**
     * Field data.
     *
     * Generates an array of objects containing field meta-data
     *
     * @return array
     */
    public function field_data()
    {
        $retval = [];
        for ($i = 0; $i < $this->num_fields(); $i++) {
            $F = new stdClass();
            $F->name = odbc_field_name($this->result_id, $i);
            $F->type = odbc_field_type($this->result_id, $i);
            $F->max_length = odbc_field_len($this->result_id, $i);
            $F->primary_key = 0;
            $F->default = '';

            $retval[] = $F;
        }

        return $retval;
    }

    // --------------------------------------------------------------------

    /**
     * Free the result.
     *
     * @return null
     */
    public function free_result()
    {
        if (is_resource($this->result_id)) {
            odbc_free_result($this->result_id);
            $this->result_id = false;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Data Seek.
     *
     * Moves the internal pointer to the desired offset.  We call
     * this internally before fetching results to make sure the
     * result set starts at zero
     *
     * @return array
     */
    public function _data_seek($n = 0)
    {
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Result - associative array.
     *
     * Returns the result set as an array
     *
     * @return array
     */
    public function _fetch_assoc()
    {
        if (function_exists('odbc_fetch_object')) {
            return odbc_fetch_array($this->result_id);
        } else {
            return $this->_odbc_fetch_array($this->result_id);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Result - object.
     *
     * Returns the result set as an object
     *
     * @return object
     */
    public function _fetch_object()
    {
        if (function_exists('odbc_fetch_object')) {
            return odbc_fetch_object($this->result_id);
        } else {
            return $this->_odbc_fetch_object($this->result_id);
        }
    }

    /**
     * Result - object.
     *
     * subsititutes the odbc_fetch_object function when
     * not available (odbc_fetch_object requires unixODBC)
     *
     * @return object
     */
    public function _odbc_fetch_object(&$odbc_result)
    {
        $rs = [];
        $rs_obj = false;
        if (odbc_fetch_into($odbc_result, $rs)) {
            foreach ($rs as $k=>$v) {
                $field_name = odbc_field_name($odbc_result, $k + 1);
                $rs_obj->$field_name = $v;
            }
        }

        return $rs_obj;
    }

    /**
     * Result - array.
     *
     * subsititutes the odbc_fetch_array function when
     * not available (odbc_fetch_array requires unixODBC)
     *
     * @return array
     */
    public function _odbc_fetch_array(&$odbc_result)
    {
        $rs = [];
        $rs_assoc = false;
        if (odbc_fetch_into($odbc_result, $rs)) {
            $rs_assoc = [];
            foreach ($rs as $k=>$v) {
                $field_name = odbc_field_name($odbc_result, $k + 1);
                $rs_assoc[$field_name] = $v;
            }
        }

        return $rs_assoc;
    }
}

/* End of file odbc_result.php */
/* Location: ./system/database/drivers/odbc/odbc_result.php */
