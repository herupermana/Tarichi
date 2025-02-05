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
 * SQLite Forge Class.
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_sqlite_forge extends CI_DB_forge
{
    /**
     * Create database.
     *
     * @param	string	the database name
     *
     * @return bool
     */
    public function _create_database()
    {
        // In SQLite, a database is created when you connect to the database.
        // We'll return TRUE so that an error isn't generated
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Drop database.
     *
     * @param	string	the database name
     *
     * @return bool
     */
    public function _drop_database($name)
    {
        if (!@file_exists($this->db->database) or !@unlink($this->db->database)) {
            if ($this->db->db_debug) {
                return $this->db->display_error('db_unable_to_drop');
            }

            return false;
        }

        return true;
    }
    // --------------------------------------------------------------------

    /**
     * Create Table.
     *
     * @param	string	the table name
     * @param	array	the fields
     * @param	mixed	primary key(s)
     * @param	mixed	key(s)
     * @param	bool	should 'IF NOT EXISTS' be added to the SQL
     *
     * @return bool
     */
    public function _create_table($table, $fields, $primary_keys, $keys, $if_not_exists)
    {
        $sql = 'CREATE TABLE ';

        // IF NOT EXISTS added to SQLite in 3.3.0
        if ($if_not_exists === true && version_compare($this->db->_version(), '3.3.0', '>=') === true) {
            $sql .= 'IF NOT EXISTS ';
        }

        $sql .= $this->db->_escape_identifiers($table).'(';
        $current_field_count = 0;

        foreach ($fields as $field=>$attributes) {
            // Numeric field names aren't allowed in databases, so if the key is
            // numeric, we know it was assigned by PHP and the developer manually
            // entered the field information, so we'll simply add it to the list
            if (is_numeric($field)) {
                $sql .= "\n\t$attributes";
            } else {
                $attributes = array_change_key_case($attributes, CASE_UPPER);

                $sql .= "\n\t".$this->db->_protect_identifiers($field);

                $sql .= ' '.$attributes['TYPE'];

                if (array_key_exists('CONSTRAINT', $attributes)) {
                    $sql .= '('.$attributes['CONSTRAINT'].')';
                }

                if (array_key_exists('UNSIGNED', $attributes) && $attributes['UNSIGNED'] === true) {
                    $sql .= ' UNSIGNED';
                }

                if (array_key_exists('DEFAULT', $attributes)) {
                    $sql .= ' DEFAULT \''.$attributes['DEFAULT'].'\'';
                }

                if (array_key_exists('NULL', $attributes) && $attributes['NULL'] === true) {
                    $sql .= ' NULL';
                } else {
                    $sql .= ' NOT NULL';
                }

                if (array_key_exists('AUTO_INCREMENT', $attributes) && $attributes['AUTO_INCREMENT'] === true) {
                    $sql .= ' AUTO_INCREMENT';
                }
            }

            // don't add a comma on the end of the last field
            if (++$current_field_count < count($fields)) {
                $sql .= ',';
            }
        }

        if (count($primary_keys) > 0) {
            $primary_keys = $this->db->_protect_identifiers($primary_keys);
            $sql .= ",\n\tPRIMARY KEY (".implode(', ', $primary_keys).')';
        }

        if (is_array($keys) && count($keys) > 0) {
            foreach ($keys as $key) {
                if (is_array($key)) {
                    $key = $this->db->_protect_identifiers($key);
                } else {
                    $key = [$this->db->_protect_identifiers($key)];
                }

                $sql .= ",\n\tUNIQUE (".implode(', ', $key).')';
            }
        }

        $sql .= "\n)";

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Drop Table.
     *
     *  Unsupported feature in SQLite
     *
     * @return bool
     */
    public function _drop_table($table)
    {
        if ($this->db->db_debug) {
            return $this->db->display_error('db_unsuported_feature');
        }

        return [];
    }

    // --------------------------------------------------------------------

    /**
     * Alter table query.
     *
     * Generates a platform-specific query so that a table can be altered
     * Called by add_column(), drop_column(), and column_alter(),
     *
     * @param	string	the ALTER type (ADD, DROP, CHANGE)
     * @param	string	the column name
     * @param	string	the table name
     * @param	string	the column definition
     * @param	string	the default value
     * @param	bool	should 'NOT NULL' be added
     * @param	string	the field after which we should add the new field
     *
     * @return object
     */
    public function _alter_table($alter_type, $table, $column_name, $column_definition = '', $default_value = '', $null = '', $after_field = '')
    {
        $sql = 'ALTER TABLE '.$this->db->_protect_identifiers($table)." $alter_type ".$this->db->_protect_identifiers($column_name);

        // DROP has everything it needs now.
        if ($alter_type == 'DROP') {
            // SQLite does not support dropping columns
            // http://www.sqlite.org/omitted.html
            // http://www.sqlite.org/faq.html#q11
            return false;
        }

        $sql .= " $column_definition";

        if ($default_value != '') {
            $sql .= " DEFAULT \"$default_value\"";
        }

        if ($null === null) {
            $sql .= ' NULL';
        } else {
            $sql .= ' NOT NULL';
        }

        if ($after_field != '') {
            $sql .= ' AFTER '.$this->db->_protect_identifiers($after_field);
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Rename a table.
     *
     * Generates a platform-specific query so that a table can be renamed
     *
     * @param	string	the old table name
     * @param	string	the new table name
     *
     * @return string
     */
    public function _rename_table($table_name, $new_table_name)
    {
        $sql = 'ALTER TABLE '.$this->db->_protect_identifiers($table_name).' RENAME TO '.$this->db->_protect_identifiers($new_table_name);

        return $sql;
    }
}

/* End of file sqlite_forge.php */
/* Location: ./system/database/drivers/sqlite/sqlite_forge.php */
