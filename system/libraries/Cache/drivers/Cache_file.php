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
 * CodeIgniter Memcached Caching Class.
 *
 * @category	Core
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link
 */
class Cache_file extends CI_Driver
{
    protected $_cache_path;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $CI = &get_instance();
        $CI->load->helper('file');

        $path = $CI->config->item('cache_path');

        $this->_cache_path = ($path == '') ? APPPATH.'cache/' : $path;
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch from cache.
     *
     * @param 	mixed		unique key id
     *
     * @return mixed data on success/false on failure
     */
    public function get($id)
    {
        if (!file_exists($this->_cache_path.$id)) {
            return false;
        }

        $data = read_file($this->_cache_path.$id);
        $data = unserialize($data);

        if (time() > $data['time'] + $data['ttl']) {
            unlink($this->_cache_path.$id);

            return false;
        }

        return $data['data'];
    }

    // ------------------------------------------------------------------------

    /**
     * Save into cache.
     *
     * @param 	string		unique key
     * @param 	mixed		data to store
     * @param 	int			length of time (in seconds) the cache is valid
     *						- Default is 60 seconds
     *
     * @return bool true on success/false on failure
     */
    public function save($id, $data, $ttl = 60)
    {
        $contents = [
            'time'		=> time(),
            'ttl'		 => $ttl,
            'data'		=> $data,
        ];

        if (write_file($this->_cache_path.$id, serialize($contents))) {
            @chmod($this->_cache_path.$id, 0777);

            return true;
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Delete from Cache.
     *
     * @param 	mixed		unique identifier of item in cache
     *
     * @return bool true on success/false on failure
     */
    public function delete($id)
    {
        return unlink($this->_cache_path.$id);
    }

    // ------------------------------------------------------------------------

    /**
     * Clean the Cache.
     *
     * @return bool false on failure/true on success
     */
    public function clean()
    {
        return delete_files($this->_cache_path);
    }

    // ------------------------------------------------------------------------

    /**
     * Cache Info.
     *
     * Not supported by file-based caching
     *
     * @param 	string	user/filehits
     *
     * @return mixed FALSE
     */
    public function cache_info($type = null)
    {
        return get_dir_file_info($this->_cache_path);
    }

    // ------------------------------------------------------------------------

    /**
     * Get Cache Metadata.
     *
     * @param 	mixed		key to get cache metadata on
     *
     * @return mixed FALSE on failure, array on success.
     */
    public function get_metadata($id)
    {
        if (!file_exists($this->_cache_path.$id)) {
            return false;
        }

        $data = read_file($this->_cache_path.$id);
        $data = unserialize($data);

        if (is_array($data)) {
            $data = $data['data'];
            $mtime = filemtime($this->_cache_path.$id);

            if (!isset($data['ttl'])) {
                return false;
            }

            return [
                'expire' 	=> $mtime + $data['ttl'],
                'mtime'		 => $mtime,
            ];
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Is supported.
     *
     * In the file driver, check to see that the cache directory is indeed writable
     *
     * @return bool
     */
    public function is_supported()
    {
        return is_really_writable($this->_cache_path);
    }

    // ------------------------------------------------------------------------
}
// End Class

/* End of file Cache_file.php */
/* Location: ./system/libraries/Cache/drivers/Cache_file.php */
