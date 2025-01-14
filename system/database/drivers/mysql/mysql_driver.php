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
 * MySQL Database Adapter Class.
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_mysql_driver extends CI_DB
{
    public $dbdriver = 'mysql';

    // The character used for escaping
    public $_escape_char = '`';

    // clause and character used for LIKE escape sequences - not used in MySQL
    public $_like_escape_str = '';
    public $_like_escape_chr = '';

    /**
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     */
    public $delete_hack = true;

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    public $_count_string = 'SELECT COUNT(*) AS ';
    public $_random_keyword = ' RAND()'; // database specific random keyword

    /**
     * Non-persistent database connection.
     *
     * @return resource
     */
    public function db_connect()
    {
        if ($this->port != '') {
            $this->hostname .= ':'.$this->port;
        }

        return @mysql_connect($this->hostname, $this->username, $this->password, true);
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection.
     *
     * @return resource
     */
    public function db_pconnect()
    {
        if ($this->port != '') {
            $this->hostname .= ':'.$this->port;
        }

        return @mysql_pconnect($this->hostname, $this->username, $this->password);
    }

    // --------------------------------------------------------------------

    /**
     * Reconnect.
     *
     * Keep / reestablish the db connection if no queries have been
     * sent for a length of time exceeding the server's idle timeout
     *
     * @return void
     */
    public function reconnect()
    {
        if (mysql_ping($this->conn_id) === false) {
            $this->conn_id = false;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Select the database.
     *
     * @return resource
     */
    public function db_select()
    {
        return @mysql_select_db($this->database, $this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Set client character set.
     *
     * @param	string
     * @param	string
     *
     * @return resource
     */
    public function db_set_charset($charset, $collation)
    {
        return @mysql_query("SET NAMES '".$this->escape_str($charset)."' COLLATE '".$this->escape_str($collation)."'", $this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Version number query string.
     *
     * @return string
     */
    public function _version()
    {
        return 'SELECT version() AS ver';
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query.
     *
     * @param	string	an SQL query
     *
     * @return resource
     */
    public function _execute($sql)
    {
        $sql = $this->_prep_query($sql);

        return @mysql_query($sql, $this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Prep the query.
     *
     * If needed, each database adapter can prep the query string
     *
     * @param	string	an SQL query
     *
     * @return string
     */
    public function _prep_query($sql)
    {
        // "DELETE FROM TABLE" returns 0 affected rows This hack modifies
        // the query so that it returns the number of affected rows
        if ($this->delete_hack === true) {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
                $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", 'DELETE FROM \\1 WHERE 1=1', $sql);
            }
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Begin Transaction.
     *
     * @return bool
     */
    public function trans_begin($test_mode = false)
    {
        if (!$this->trans_enabled) {
            return true;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return true;
        }

        // Reset the transaction failure flag.
        // If the $test_mode flag is set to TRUE transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_trans_failure = ($test_mode === true) ? true : false;

        $this->simple_query('SET AUTOCOMMIT=0');
        $this->simple_query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Commit Transaction.
     *
     * @return bool
     */
    public function trans_commit()
    {
        if (!$this->trans_enabled) {
            return true;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return true;
        }

        $this->simple_query('COMMIT');
        $this->simple_query('SET AUTOCOMMIT=1');

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Rollback Transaction.
     *
     * @return bool
     */
    public function trans_rollback()
    {
        if (!$this->trans_enabled) {
            return true;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return true;
        }

        $this->simple_query('ROLLBACK');
        $this->simple_query('SET AUTOCOMMIT=1');

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Escape String.
     *
     * @param	string
     * @param	bool	whether or not the string will be used in a LIKE condition
     *
     * @return string
     */
    public function escape_str($str, $like = false)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escape_str($val, $like);
            }

            return $str;
        }

        if (function_exists('mysql_real_escape_string') and is_resource($this->conn_id)) {
            $str = mysql_real_escape_string($str, $this->conn_id);
        } elseif (function_exists('mysql_escape_string')) {
            $str = mysql_escape_string($str);
        } else {
            $str = addslashes($str);
        }

        // escape LIKE condition wildcards
        if ($like === true) {
            $str = str_replace(['%', '_'], ['\\%', '\\_'], $str);
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Affected Rows.
     *
     * @return int
     */
    public function affected_rows()
    {
        return @mysql_affected_rows($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID.
     *
     * @return int
     */
    public function insert_id()
    {
        return @mysql_insert_id($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * "Count All" query.
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @param	string
     *
     * @return string
     */
    public function count_all($table = '')
    {
        if ($table == '') {
            return 0;
        }

        $query = $this->query($this->_count_string.$this->_protect_identifiers('numrows').' FROM '.$this->_protect_identifiers($table, true, null, false));

        if ($query->num_rows() == 0) {
            return 0;
        }

        $row = $query->row();

        return (int) $row->numrows;
    }

    // --------------------------------------------------------------------

    /**
     * List table query.
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @param	bool
     *
     * @return string
     */
    public function _list_tables($prefix_limit = false)
    {
        $sql = 'SHOW TABLES FROM '.$this->_escape_char.$this->database.$this->_escape_char;

        if ($prefix_limit !== false and $this->dbprefix != '') {
            $sql .= " LIKE '".$this->escape_like_str($this->dbprefix)."%'";
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Show column query.
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @param	string	the table name
     *
     * @return string
     */
    public function _list_columns($table = '')
    {
        return 'SHOW COLUMNS FROM '.$this->_protect_identifiers($table, true, null, false);
    }

    // --------------------------------------------------------------------

    /**
     * Field data query.
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @param	string	the table name
     *
     * @return object
     */
    public function _field_data($table)
    {
        return 'SELECT * FROM '.$table.' LIMIT 1';
    }

    // --------------------------------------------------------------------

    /**
     * The error message string.
     *
     * @return string
     */
    public function _error_message()
    {
        return mysql_error($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * The error message number.
     *
     * @return int
     */
    public function _error_number()
    {
        return mysql_errno($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Escape the SQL Identifiers.
     *
     * This function escapes column and table names
     *
     * @param	string
     *
     * @return string
     */
    public function _escape_identifiers($item)
    {
        if ($this->_escape_char == '') {
            return $item;
        }

        foreach ($this->_reserved_identifiers as $id) {
            if (strpos($item, '.'.$id) !== false) {
                $str = $this->_escape_char.str_replace('.', $this->_escape_char.'.', $item);

                // remove duplicates if the user already included the escape
                return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
            }
        }

        if (strpos($item, '.') !== false) {
            $str = $this->_escape_char.str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item).$this->_escape_char;
        } else {
            $str = $this->_escape_char.$item.$this->_escape_char;
        }

        // remove duplicates if the user already included the escape
        return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
    }

    // --------------------------------------------------------------------

    /**
     * From Tables.
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @param	type
     *
     * @return type
     */
    public function _from_tables($tables)
    {
        if (!is_array($tables)) {
            $tables = [$tables];
        }

        return '('.implode(', ', $tables).')';
    }

    // --------------------------------------------------------------------

    /**
     * Insert statement.
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     *
     * @return string
     */
    public function _insert($table, $keys, $values)
    {
        return 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
    }

    // --------------------------------------------------------------------

    /**
     * Replace statement.
     *
     * Generates a platform-specific replace string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     *
     * @return string
     */
    public function _replace($table, $keys, $values)
    {
        return 'REPLACE INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
    }

    // --------------------------------------------------------------------

    /**
     * Insert_batch statement.
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     *
     * @return string
     */
    public function _insert_batch($table, $keys, $values)
    {
        return 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES '.implode(', ', $values);
    }

    // --------------------------------------------------------------------

    /**
     * Update statement.
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @param	array	the orderby clause
     * @param	array	the limit clause
     *
     * @return string
     */
    public function _update($table, $values, $where, $orderby = [], $limit = false)
    {
        foreach ($values as $key => $val) {
            $valstr[] = $key.' = '.$val;
        }

        $limit = (!$limit) ? '' : ' LIMIT '.$limit;

        $orderby = (count($orderby) >= 1) ? ' ORDER BY '.implode(', ', $orderby) : '';

        $sql = 'UPDATE '.$table.' SET '.implode(', ', $valstr);

        $sql .= ($where != '' and count($where) >= 1) ? ' WHERE '.implode(' ', $where) : '';

        $sql .= $orderby.$limit;

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Update_Batch statement.
     *
     * Generates a platform-specific batch update string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     *
     * @return string
     */
    public function _update_batch($table, $values, $index, $where = null)
    {
        $ids = [];
        $where = ($where != '' and count($where) >= 1) ? implode(' ', $where).' AND ' : '';

        foreach ($values as $key => $val) {
            $ids[] = $val[$index];

            foreach (array_keys($val) as $field) {
                if ($field != $index) {
                    $final[$field][] = 'WHEN '.$index.' = '.$val[$index].' THEN '.$val[$field];
                }
            }
        }

        $sql = 'UPDATE '.$table.' SET ';
        $cases = '';

        foreach ($final as $k => $v) {
            $cases .= $k.' = CASE '."\n";
            foreach ($v as $row) {
                $cases .= $row."\n";
            }

            $cases .= 'ELSE '.$k.' END, ';
        }

        $sql .= substr($cases, 0, -2);

        $sql .= ' WHERE '.$where.$index.' IN ('.implode(',', $ids).')';

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Truncate statement.
     *
     * Generates a platform-specific truncate string from the supplied data
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @param	string	the table name
     *
     * @return string
     */
    public function _truncate($table)
    {
        return 'TRUNCATE '.$table;
    }

    // --------------------------------------------------------------------

    /**
     * Delete statement.
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the where clause
     * @param	string	the limit clause
     *
     * @return string
     */
    public function _delete($table, $where = [], $like = [], $limit = false)
    {
        $conditions = '';

        if (count($where) > 0 or count($like) > 0) {
            $conditions = "\nWHERE ";
            $conditions .= implode("\n", $this->ar_where);

            if (count($where) > 0 && count($like) > 0) {
                $conditions .= ' AND ';
            }
            $conditions .= implode("\n", $like);
        }

        $limit = (!$limit) ? '' : ' LIMIT '.$limit;

        return 'DELETE FROM '.$table.$conditions.$limit;
    }

    // --------------------------------------------------------------------

    /**
     * Limit string.
     *
     * Generates a platform-specific LIMIT clause
     *
     * @param	string	the sql query string
     * @param	int	the number of rows to limit the query to
     * @param	int	the offset value
     *
     * @return string
     */
    public function _limit($sql, $limit, $offset)
    {
        if ($offset == 0) {
            $offset = '';
        } else {
            $offset .= ', ';
        }

        return $sql.'LIMIT '.$offset.$limit;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection.
     *
     * @param	resource
     *
     * @return void
     */
    public function _close($conn_id)
    {
        @mysql_close($conn_id);
    }
}

/* End of file mysql_driver.php */
/* Location: ./system/database/drivers/mysql/mysql_driver.php */
