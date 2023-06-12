<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * CodeIgniter.
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2006 - 2011 EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 *
 * @link		http://codeigniter.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Dummy Caching Class.
 *
 * @category	Core
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link
 */
class Cache_dummy extends CI_Driver
{
    /**
     * Get.
     *
     * Since this is the dummy class, it's always going to return FALSE.
     *
     * @param 	string
     *
     * @return bool FALSE
     */
    public function get($id)
    {
        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Cache Save.
     *
     * @param 	string		Unique Key
     * @param 	mixed		Data to store
     * @param 	int			Length of time (in seconds) to cache the data
     *
     * @return bool TRUE, Simulating success
     */
    public function save($id, $data, $ttl = 60)
    {
        return true;
    }

    // ------------------------------------------------------------------------

    /**
     * Delete from Cache.
     *
     * @param 	mixed		unique identifier of the item in the cache
     * @param 	bool		TRUE, simulating success
     */
    public function delete($id)
    {
        return true;
    }

    // ------------------------------------------------------------------------

    /**
     * Clean the cache.
     *
     * @return bool TRUE, simulating success
     */
    public function clean()
    {
        return true;
    }

    // ------------------------------------------------------------------------

    /**
     * Cache Info.
     *
     * @param 	string		user/filehits
     *
     * @return bool FALSE
     */
    public function cache_info($type = null)
    {
        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Cache Metadata.
     *
     * @param 	mixed		key to get cache metadata on
     *
     * @return bool FALSE
     */
    public function get_metadata($id)
    {
        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Is this caching driver supported on the system?
     * Of course this one is.
     *
     * @return TRUE;
     */
    public function is_supported()
    {
        return true;
    }

    // ------------------------------------------------------------------------
}
// End Class

/* End of file Cache_apc.php */
/* Location: ./system/libraries/Cache/drivers/Cache_apc.php */
