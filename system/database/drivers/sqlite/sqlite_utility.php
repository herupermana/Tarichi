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
 * SQLite Utility Class.
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_sqlite_utility extends CI_DB_utility
{
    /**
     * List databases.
     *
     * I don't believe you can do a database listing with SQLite
     * since each database is its own file.  I suppose we could
     * try reading a directory looking for SQLite files, but
     * that doesn't seem like a terribly good idea
     *
     * @return bool
     */
    public function _list_databases()
    {
        if ($this->db_debug) {
            return $this->db->display_error('db_unsuported_feature');
        }

        return [];
    }

    // --------------------------------------------------------------------

    /**
     * Optimize table query.
     *
     * Is optimization even supported in SQLite?
     *
     * @param	string	the table name
     *
     * @return object
     */
    public function _optimize_table($table)
    {
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Repair table query.
     *
     * Are table repairs even supported in SQLite?
     *
     * @param	string	the table name
     *
     * @return object
     */
    public function _repair_table($table)
    {
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * SQLite Export.
     *
     * @param	array	Preferences
     *
     * @return mixed
     */
    public function _backup($params = [])
    {
        // Currently unsupported
        return $this->db->display_error('db_unsuported_feature');
    }
}

/* End of file sqlite_utility.php */
/* Location: ./system/database/drivers/sqlite/sqlite_utility.php */
