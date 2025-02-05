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
 * Postgres Result Class.
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_postgre_result extends CI_DB_result
{
    /**
     * Number of rows in the result set.
     *
     * @return int
     */
    public function num_rows()
    {
        return @pg_num_rows($this->result_id);
    }

    // --------------------------------------------------------------------

    /**
     * Number of fields in the result set.
     *
     * @return int
     */
    public function num_fields()
    {
        return @pg_num_fields($this->result_id);
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
            $field_names[] = pg_field_name($this->result_id, $i);
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
            $F->name = pg_field_name($this->result_id, $i);
            $F->type = pg_field_type($this->result_id, $i);
            $F->max_length = pg_field_size($this->result_id, $i);
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
            pg_free_result($this->result_id);
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
        return pg_result_seek($this->result_id, $n);
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
        return pg_fetch_assoc($this->result_id);
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
        return pg_fetch_object($this->result_id);
    }
}

/* End of file postgre_result.php */
/* Location: ./system/database/drivers/postgre/postgre_result.php */
