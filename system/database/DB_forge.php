<?php

 if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * Code Igniter.
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
 * Database Utility Class.
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_forge
{
    public $fields = [];
    public $keys = [];
    public $primary_keys = [];
    public $db_char_set = '';

    /**
     * Constructor.
     *
     * Grabs the CI super object instance so we can access it.
     */
    public function CI_DB_forge()
    {
        // Assign the main database object to $this->db
        $CI = &get_instance();
        $this->db = &$CI->db;
        log_message('debug', 'Database Forge Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Create database.
     *
     * @param	string	the database name
     *
     * @return bool
     */
    public function create_database($db_name)
    {
        $sql = $this->_create_database($db_name);

        if (is_bool($sql)) {
            return $sql;
        }

        return $this->db->query($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Drop database.
     *
     * @param	string	the database name
     *
     * @return bool
     */
    public function drop_database($db_name)
    {
        $sql = $this->_drop_database($db_name);

        if (is_bool($sql)) {
            return $sql;
        }

        return $this->db->query($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Add Key.
     *
     * @param	string	key
     * @param	string	type
     *
     * @return void
     */
    public function add_key($key = '', $primary = false)
    {
        if (is_array($key)) {
            foreach ($key as $one) {
                $this->add_key($one, $primary);
            }

            return;
        }

        if ($key == '') {
            show_error('Key information is required for that operation.');
        }

        if ($primary === true) {
            $this->primary_keys[] = $key;
        } else {
            $this->keys[] = $key;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Add Field.
     *
     * @param	string	collation
     *
     * @return void
     */
    public function add_field($field = '')
    {
        if ($field == '') {
            show_error('Field information is required.');
        }

        if (is_string($field)) {
            if ($field == 'id') {
                $this->add_field([
                    'id' => [
                        'type'           => 'INT',
                        'constraint'     => 9,
                        'auto_increment' => true,
                    ],
                ]);
                $this->add_key('id', true);
            } else {
                if (strpos($field, ' ') === false) {
                    show_error('Field information is required for that operation.');
                }

                $this->fields[] = $field;
            }
        }

        if (is_array($field)) {
            $this->fields = array_merge($this->fields, $field);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Create Table.
     *
     * @param	string	the table name
     *
     * @return bool
     */
    public function create_table($table = '', $if_not_exists = false)
    {
        if ($table == '') {
            show_error('A table name is required for that operation.');
        }

        if (count($this->fields) == 0) {
            show_error('Field information is required.');
        }

        $sql = $this->_create_table($this->db->dbprefix.$table, $this->fields, $this->primary_keys, $this->keys, $if_not_exists);

        $this->_reset();

        return $this->db->query($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Drop Table.
     *
     * @param	string	the table name
     *
     * @return bool
     */
    public function drop_table($table_name)
    {
        $sql = $this->_drop_table($this->db->dbprefix.$table_name);

        if (is_bool($sql)) {
            return $sql;
        }

        return $this->db->query($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Rename Table.
     *
     * @param	string	the old table name
     * @param	string	the new table name
     *
     * @return bool
     */
    public function rename_table($table_name, $new_table_name)
    {
        if ($table_name == '' or $new_table_name == '') {
            show_error('A table name is required for that operation.');
        }

        $sql = $this->_rename_table($table_name, $new_table_name);

        return $this->db->query($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Column Add.
     *
     * @param	string	the table name
     * @param	string	the column name
     * @param	string	the column definition
     *
     * @return bool
     */
    public function add_column($table = '', $field = [], $after_field = '')
    {
        if ($table == '') {
            show_error('A table name is required for that operation.');
        }

        // add field info into field array, but we can only do one at a time
        // so we cycle through

        foreach ($field as $k => $v) {
            $this->add_field([$k => $field[$k]]);

            if (count($this->fields) == 0) {
                show_error('Field information is required.');
            }

            $sql = $this->_alter_table('ADD', $this->db->dbprefix.$table, $this->fields, $after_field);

            $this->_reset();

            if ($this->db->query($sql) === false) {
                return false;
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Column Drop.
     *
     * @param	string	the table name
     * @param	string	the column name
     *
     * @return bool
     */
    public function drop_column($table = '', $column_name = '')
    {
        if ($table == '') {
            show_error('A table name is required for that operation.');
        }

        if ($column_name == '') {
            show_error('A column name is required for that operation.');
        }

        $sql = $this->_alter_table('DROP', $this->db->dbprefix.$table, $column_name);

        return $this->db->query($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Column Modify.
     *
     * @param	string	the table name
     * @param	string	the column name
     * @param	string	the column definition
     *
     * @return bool
     */
    public function modify_column($table = '', $field = [])
    {
        if ($table == '') {
            show_error('A table name is required for that operation.');
        }

        // add field info into field array, but we can only do one at a time
        // so we cycle through

        foreach ($field as $k => $v) {
            // If no name provided, use the current name
            if (!isset($field[$k]['name'])) {
                $field[$k]['name'] = $k;
            }

            $this->add_field([$k => $field[$k]]);

            if (count($this->fields) == 0) {
                show_error('Field information is required.');
            }

            $sql = $this->_alter_table('CHANGE', $this->db->dbprefix.$table, $this->fields);

            $this->_reset();

            if ($this->db->query($sql) === false) {
                return false;
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Reset.
     *
     * Resets table creation vars
     *
     * @return void
     */
    public function _reset()
    {
        $this->fields = [];
        $this->keys = [];
        $this->primary_keys = [];
    }
}

/* End of file DB_forge.php */
/* Location: ./system/database/DB_forge.php */
