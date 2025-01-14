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
 * Database Result Class.
 *
 * This is the platform-independent result class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_result
{
    public $conn_id = null;
    public $result_id = null;
    public $result_array = [];
    public $result_object = [];
    public $custom_result_object = [];
    public $current_row = 0;
    public $num_rows = 0;
    public $row_data = null;

    /**
     * Query result.  Acts as a wrapper function for the following functions.
     *
     * @param	string	can be "object" or "array"
     *
     * @return mixed either a result object or array
     */
    public function result($type = 'object')
    {
        if ($type == 'array') {
            return $this->result_array();
        } elseif ($type == 'object') {
            return $this->result_object();
        } else {
            return $this->custom_result_object($type);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Custom query result.
     *
     * @param  class_name  A string that represents the type of object you want back
     *
     * @return array of objects
     */
    public function custom_result_object($class_name)
    {
        if (array_key_exists($class_name, $this->custom_result_object)) {
            return $this->custom_result_object[$class_name];
        }

        if ($this->result_id === false or $this->num_rows() == 0) {
            return [];
        }

        // add the data to the object
        $this->_data_seek(0);
        $result_object = [];
        while ($row = $this->_fetch_object()) {
            $object = new $class_name();
            foreach ($row as $key => $value) {
                $object->$key = $value;
            }
            $result_object[] = $object;
        }

        // return the array
        return $this->custom_result_object[$class_name] = $result_object;
    }

    /**
     * Query result.  "object" version.
     *
     * @return object
     */
    public function result_object()
    {
        if (count($this->result_object) > 0) {
            return $this->result_object;
        }

        // In the event that query caching is on the result_id variable
        // will return FALSE since there isn't a valid SQL resource so
        // we'll simply return an empty array.
        if ($this->result_id === false or $this->num_rows() == 0) {
            return [];
        }

        $this->_data_seek(0);
        while ($row = $this->_fetch_object()) {
            $this->result_object[] = $row;
        }

        return $this->result_object;
    }

    // --------------------------------------------------------------------

    /**
     * Query result.  "array" version.
     *
     * @return array
     */
    public function result_array()
    {
        if (count($this->result_array) > 0) {
            return $this->result_array;
        }

        // In the event that query caching is on the result_id variable
        // will return FALSE since there isn't a valid SQL resource so
        // we'll simply return an empty array.
        if ($this->result_id === false or $this->num_rows() == 0) {
            return [];
        }

        $this->_data_seek(0);
        while ($row = $this->_fetch_assoc()) {
            $this->result_array[] = $row;
        }

        return $this->result_array;
    }

    // --------------------------------------------------------------------

    /**
     * Query result.  Acts as a wrapper function for the following functions.
     *
     * @param	string
     * @param	string	can be "object" or "array"
     *
     * @return mixed either a result object or array
     */
    public function row($n = 0, $type = 'object')
    {
        if (!is_numeric($n)) {
            // We cache the row data for subsequent uses
            if (!is_array($this->row_data)) {
                $this->row_data = $this->row_array(0);
            }

            // array_key_exists() instead of isset() to allow for MySQL NULL values
            if (array_key_exists($n, $this->row_data)) {
                return $this->row_data[$n];
            }
            // reset the $n variable if the result was not achieved
            $n = 0;
        }

        if ($type == 'object') {
            return $this->row_object($n);
        } elseif ($type == 'array') {
            return $this->row_array($n);
        } else {
            return $this->custom_row_object($n, $type);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Assigns an item into a particular column slot.
     *
     * @return object
     */
    public function set_row($key, $value = null)
    {
        // We cache the row data for subsequent uses
        if (!is_array($this->row_data)) {
            $this->row_data = $this->row_array(0);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->row_data[$k] = $v;
            }

            return;
        }

        if ($key != '' and !is_null($value)) {
            $this->row_data[$key] = $value;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Returns a single result row - custom object version.
     *
     * @return object
     */
    public function custom_row_object($n, $type)
    {
        $result = $this->custom_result_object($type);

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row and isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

    /**
     * Returns a single result row - object version.
     *
     * @return object
     */
    public function row_object($n = 0)
    {
        $result = $this->result_object();

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row and isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

    // --------------------------------------------------------------------

    /**
     * Returns a single result row - array version.
     *
     * @return array
     */
    public function row_array($n = 0)
    {
        $result = $this->result_array();

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row and isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

    // --------------------------------------------------------------------

    /**
     * Returns the "first" row.
     *
     * @return object
     */
    public function first_row($type = 'object')
    {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        return $result[0];
    }

    // --------------------------------------------------------------------

    /**
     * Returns the "last" row.
     *
     * @return object
     */
    public function last_row($type = 'object')
    {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        return $result[count($result) - 1];
    }

    // --------------------------------------------------------------------

    /**
     * Returns the "next" row.
     *
     * @return object
     */
    public function next_row($type = 'object')
    {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        if (isset($result[$this->current_row + 1])) {
            $this->current_row++;
        }

        return $result[$this->current_row];
    }

    // --------------------------------------------------------------------

    /**
     * Returns the "previous" row.
     *
     * @return object
     */
    public function previous_row($type = 'object')
    {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        if (isset($result[$this->current_row - 1])) {
            $this->current_row--;
        }

        return $result[$this->current_row];
    }

    // --------------------------------------------------------------------

    /**
     * The following functions are normally overloaded by the identically named
     * methods in the platform-specific driver -- except when query caching
     * is used.  When caching is enabled we do not load the other driver.
     * These functions are primarily here to prevent undefined function errors
     * when a cached result object is in use.  They are not otherwise fully
     * operational due to the unavailability of the database resource IDs with
     * cached results.
     */
    public function num_rows()
    {
        return $this->num_rows;
    }

    public function num_fields()
    {
        return 0;
    }

    public function list_fields()
    {
        return [];
    }

    public function field_data()
    {
        return [];
    }

    public function free_result()
    {
        return true;
    }

    public function _data_seek()
    {
        return true;
    }

    public function _fetch_assoc()
    {
        return [];
    }

    public function _fetch_object()
    {
        return [];
    }
}
// END DB_result class

/* End of file DB_result.php */
/* Location: ./system/database/DB_result.php */
