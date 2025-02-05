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
 * Loader Class.
 *
 * Loads views and files
 *
 * @author		ExpressionEngine Dev Team
 *
 * @category	Loader
 *
 * @link		http://codeigniter.com/user_guide/libraries/loader.html
 */
class CI_Loader
{
    // All these are set automatically. Don't mess with them.
    public $_ci_ob_level;
    public $_ci_view_path = '';
    public $_ci_library_paths = [];
    public $_ci_model_paths = [];
    public $_ci_helper_paths = [];
    public $_base_classes = []; // Set by the controller class
    public $_ci_cached_vars = [];
    public $_ci_classes = [];
    public $_ci_loaded_files = [];
    public $_ci_models = [];
    public $_ci_helpers = [];
    public $_ci_varmap = ['unit_test' => 'unit', 'user_agent' => 'agent'];

    /**
     * Constructor.
     *
     * Sets the path to the view files and gets the initial output buffering level
     */
    public function __construct()
    {
        $this->_ci_view_path = APPPATH.'views/';
        $this->_ci_ob_level = ob_get_level();
        $this->_ci_library_paths = [APPPATH, BASEPATH];
        $this->_ci_helper_paths = [APPPATH, BASEPATH];
        $this->_ci_model_paths = [APPPATH];

        log_message('debug', 'Loader Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Class Loader.
     *
     * This function lets users load and instantiate classes.
     * It is designed to be called from a user's app controllers.
     *
     * @param	string	the name of the class
     * @param	mixed	the optional parameters
     * @param	string	an optional object name
     *
     * @return void
     */
    public function library($library = '', $params = null, $object_name = null)
    {
        if (is_array($library)) {
            foreach ($library as $read) {
                $this->library($read);
            }

            return;
        }

        if ($library == '' or isset($this->_base_classes[$library])) {
            return false;
        }

        if (!is_null($params) && !is_array($params)) {
            $params = null;
        }

        if (is_array($library)) {
            foreach ($library as $class) {
                $this->_ci_load_class($class, $params, $object_name);
            }
        } else {
            $this->_ci_load_class($library, $params, $object_name);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Model Loader.
     *
     * This function lets users load and instantiate models.
     *
     * @param	string	the name of the class
     * @param	string	name for the model
     * @param	bool	database connection
     *
     * @return void
     */
    public function model($model, $name = '', $db_conn = false)
    {
        if (is_array($model)) {
            foreach ($model as $babe) {
                $this->model($babe);
            }

            return;
        }

        if ($model == '') {
            return;
        }

        $path = '';

        // Is the model in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($model, '/')) !== false) {
            // The path is in front of the last slash
            $path = substr($model, 0, $last_slash + 1);

            // And the model name behind it
            $model = substr($model, $last_slash + 1);
        }

        if ($name == '') {
            $name = $model;
        }

        if (in_array($name, $this->_ci_models, true)) {
            return;
        }

        $CI = &get_instance();
        if (isset($CI->$name)) {
            show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
        }

        $model = strtolower($model);

        foreach ($this->_ci_model_paths as $mod_path) {
            if (!file_exists($mod_path.'models/'.$path.$model.EXT)) {
                continue;
            }

            if ($db_conn !== false and !class_exists('CI_DB')) {
                if ($db_conn === true) {
                    $db_conn = '';
                }

                $CI->load->database($db_conn, false, true);
            }

            if (!class_exists('CI_Model')) {
                load_class('Model', 'core');
            }

            require_once $mod_path.'models/'.$path.$model.EXT;

            $model = ucfirst($model);

            $CI->$name = new $model();

            $this->_ci_models[] = $name;

            return;
        }

        // couldn't find the model
        show_error('Unable to locate the model you have specified: '.$model);
    }

    // --------------------------------------------------------------------

    /**
     * Database Loader.
     *
     * @param	string	the DB credentials
     * @param	bool	whether to return the DB object
     * @param	bool	whether to enable active record (this allows us to override the config setting)
     *
     * @return object
     */
    public function database($params = '', $return = false, $active_record = null)
    {
        // Grab the super object
        $CI = &get_instance();

        // Do we even need to load the database class?
        if (class_exists('CI_DB') and $return == false and $active_record == null and isset($CI->db) and is_object($CI->db)) {
            return false;
        }

        require_once BASEPATH.'database/DB'.EXT;

        if ($return === true) {
            return DB($params, $active_record);
        }

        // Initialize the db variable.  Needed to prevent
        // reference errors with some configurations
        $CI->db = '';

        // Load the DB class
        $CI->db = &DB($params, $active_record);
    }

    // --------------------------------------------------------------------

    /**
     * Load the Utilities Class.
     *
     * @return string
     */
    public function dbutil()
    {
        if (!class_exists('CI_DB')) {
            $this->database();
        }

        $CI = &get_instance();

        // for backwards compatibility, load dbforge so we can extend dbutils off it
        // this use is deprecated and strongly discouraged
        $CI->load->dbforge();

        require_once BASEPATH.'database/DB_utility'.EXT;
        require_once BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_utility'.EXT;
        $class = 'CI_DB_'.$CI->db->dbdriver.'_utility';

        $CI->dbutil = new $class();
    }

    // --------------------------------------------------------------------

    /**
     * Load the Database Forge Class.
     *
     * @return string
     */
    public function dbforge()
    {
        if (!class_exists('CI_DB')) {
            $this->database();
        }

        $CI = &get_instance();

        require_once BASEPATH.'database/DB_forge'.EXT;
        require_once BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_forge'.EXT;
        $class = 'CI_DB_'.$CI->db->dbdriver.'_forge';

        $CI->dbforge = new $class();
    }

    // --------------------------------------------------------------------

    /**
     * Load View.
     *
     * This function is used to load a "view" file.  It has three parameters:
     *
     * 1. The name of the "view" file to be included.
     * 2. An associative array of data to be extracted for use in the view.
     * 3. TRUE/FALSE - whether to return the data or load it.  In
     * some cases it's advantageous to be able to return data so that
     * a developer can process it in some way.
     *
     * @param	string
     * @param	array
     * @param	bool
     *
     * @return void
     */
    public function view($view, $vars = [], $return = false)
    {
        return $this->_ci_load(['_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return]);
    }

    // --------------------------------------------------------------------

    /**
     * Load File.
     *
     * This is a generic file loader
     *
     * @param	string
     * @param	bool
     *
     * @return string
     */
    public function file($path, $return = false)
    {
        return $this->_ci_load(['_ci_path' => $path, '_ci_return' => $return]);
    }

    // --------------------------------------------------------------------

    /**
     * Set Variables.
     *
     * Once variables are set they become available within
     * the controller class and its "view" files.
     *
     * @param	array
     *
     * @return void
     */
    public function vars($vars = [], $val = '')
    {
        if ($val != '' and is_string($vars)) {
            $vars = [$vars => $val];
        }

        $vars = $this->_ci_object_to_array($vars);

        if (is_array($vars) and count($vars) > 0) {
            foreach ($vars as $key => $val) {
                $this->_ci_cached_vars[$key] = $val;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Load Helper.
     *
     * This function loads the specified helper file.
     *
     * @param	mixed
     *
     * @return void
     */
    public function helper($helpers = [])
    {
        foreach ($this->_ci_prep_filename($helpers, '_helper') as $helper) {
            if (isset($this->_ci_helpers[$helper])) {
                continue;
            }

            $ext_helper = APPPATH.'helpers/'.config_item('subclass_prefix').$helper.EXT;

            // Is this a helper extension request?
            if (file_exists($ext_helper)) {
                $base_helper = BASEPATH.'helpers/'.$helper.EXT;

                if (!file_exists($base_helper)) {
                    show_error('Unable to load the requested file: helpers/'.$helper.EXT);
                }

                include_once $ext_helper;
                include_once $base_helper;

                $this->_ci_helpers[$helper] = true;
                log_message('debug', 'Helper loaded: '.$helper);
                continue;
            }

            // Try to load the helper
            foreach ($this->_ci_helper_paths as $path) {
                if (file_exists($path.'helpers/'.$helper.EXT)) {
                    include_once $path.'helpers/'.$helper.EXT;

                    $this->_ci_helpers[$helper] = true;
                    log_message('debug', 'Helper loaded: '.$helper);
                    break;
                }
            }

            // unable to load the helper
            if (!isset($this->_ci_helpers[$helper])) {
                show_error('Unable to load the requested file: helpers/'.$helper.EXT);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Load Helpers.
     *
     * This is simply an alias to the above function in case the
     * user has written the plural form of this function.
     *
     * @param	array
     *
     * @return void
     */
    public function helpers($helpers = [])
    {
        $this->helper($helpers);
    }

    // --------------------------------------------------------------------

    /**
     * Loads a language file.
     *
     * @param	array
     * @param	string
     *
     * @return void
     */
    public function language($file = [], $lang = '')
    {
        $CI = &get_instance();

        if (!is_array($file)) {
            $file = [$file];
        }

        foreach ($file as $langfile) {
            $CI->lang->load($langfile, $lang);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Loads a config file.
     *
     * @param	string
     *
     * @return void
     */
    public function config($file = '', $use_sections = false, $fail_gracefully = false)
    {
        $CI = &get_instance();
        $CI->config->load($file, $use_sections, $fail_gracefully);
    }

    // --------------------------------------------------------------------

    /**
     * Driver.
     *
     * Loads a driver library
     *
     * @param	string	the name of the class
     * @param	mixed	the optional parameters
     * @param	string	an optional object name
     *
     * @return void
     */
    public function driver($library = '', $params = null, $object_name = null)
    {
        if (!class_exists('CI_Driver_Library')) {
            // we aren't instantiating an object here, that'll be done by the Library itself
            require BASEPATH.'libraries/Driver'.EXT;
        }

        // We can save the loader some time since Drivers will *always* be in a subfolder,
        // and typically identically named to the library
        if (!strpos($library, '/')) {
            $library = ucfirst($library).'/'.$library;
        }

        return $this->library($library, $params, $object_name);
    }

    // --------------------------------------------------------------------

    /**
     * Add Package Path.
     *
     * Prepends a parent path to the library, model, helper, and config path arrays
     *
     * @param	string
     *
     * @return void
     */
    public function add_package_path($path)
    {
        $path = rtrim($path, '/').'/';

        array_unshift($this->_ci_library_paths, $path);
        array_unshift($this->_ci_model_paths, $path);
        array_unshift($this->_ci_helper_paths, $path);

        // Add config file path
        $config = &$this->_ci_get_component('config');
        array_unshift($config->_config_paths, $path);
    }

    // --------------------------------------------------------------------

    /**
     * Get Package Paths.
     *
     * Return a list of all package paths, by default it will ignore BASEPATH.
     *
     * @param	string
     *
     * @return void
     */
    public function get_package_paths($include_base = false)
    {
        return $include_base === true ? $this->_ci_library_paths : $this->_ci_model_paths;
    }

    // --------------------------------------------------------------------

    /**
     * Remove Package Path.
     *
     * Remove a path from the library, model, and helper path arrays if it exists
     * If no path is provided, the most recently added path is removed.
     *
     * @param	type
     *
     * @return type
     */
    public function remove_package_path($path = '', $remove_config_path = true)
    {
        $config = &$this->_ci_get_component('config');

        if ($path == '') {
            $void = array_shift($this->_ci_library_paths);
            $void = array_shift($this->_ci_model_paths);
            $void = array_shift($this->_ci_helper_paths);
            $void = array_shift($config->_config_paths);
        } else {
            $path = rtrim($path, '/').'/';

            foreach (['_ci_library_paths', '_ci_model_paths', '_ci_helper_paths'] as $var) {
                if (($key = array_search($path, $this->{$var})) !== false) {
                    unset($this->{$var}[$key]);
                }
            }

            if (($key = array_search($path, $config->_config_paths)) !== false) {
                unset($config->_config_paths[$key]);
            }
        }

        // make sure the application default paths are still in the array
        $this->_ci_library_paths = array_unique(array_merge($this->_ci_library_paths, [APPPATH, BASEPATH]));
        $this->_ci_helper_paths = array_unique(array_merge($this->_ci_helper_paths, [APPPATH, BASEPATH]));
        $this->_ci_model_paths = array_unique(array_merge($this->_ci_model_paths, [APPPATH]));
        $config->_config_paths = array_unique(array_merge($config->_config_paths, [APPPATH]));
    }

    // --------------------------------------------------------------------

    /**
     * Loader.
     *
     * This function is used to load views and files.
     * Variables are prefixed with _ci_ to avoid symbol collision with
     * variables made available to view files
     *
     * @param	array
     *
     * @return void
     */
    public function _ci_load($_ci_data)
    {
        // Set the default data variables
        foreach (['_ci_view', '_ci_vars', '_ci_path', '_ci_return'] as $_ci_val) {
            $$_ci_val = (!isset($_ci_data[$_ci_val])) ? false : $_ci_data[$_ci_val];
        }

        // Set the path to the requested file
        if ($_ci_path == '') {
            $_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
            $_ci_file = ($_ci_ext == '') ? $_ci_view.EXT : $_ci_view;
            $_ci_path = $this->_ci_view_path.$_ci_file;
        } else {
            $_ci_x = explode('/', $_ci_path);
            $_ci_file = end($_ci_x);
        }

        if (!file_exists($_ci_path)) {
            show_error('Unable to load the requested file: '.$_ci_file);
        }

        // This allows anything loaded using $this->load (views, files, etc.)
        // to become accessible from within the Controller and Model functions.

        $_ci_CI = &get_instance();
        foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var) {
            if (!isset($this->$_ci_key)) {
                $this->$_ci_key = &$_ci_CI->$_ci_key;
            }
        }

        /*
         * Extract and cache variables
         *
         * You can either set variables using the dedicated $this->load_vars()
         * function or via the second parameter of this function. We'll merge
         * the two types and cache them so that views that are embedded within
         * other views can have access to these variables.
         */
        if (is_array($_ci_vars)) {
            $this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
        }
        extract($this->_ci_cached_vars);

        /*
         * Buffer the output
         *
         * We buffer the output for two reasons:
         * 1. Speed. You get a significant speed boost.
         * 2. So that the final rendered template can be
         * post-processed by the output class.  Why do we
         * need post processing?  For one thing, in order to
         * show the elapsed page load time.  Unless we
         * can intercept the content right before it's sent to
         * the browser and then stop the timer it won't be accurate.
         */
        ob_start();

        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.

        if ((bool) @ini_get('short_open_tag') === false and config_item('rewrite_short_tags') == true) {
            echo eval('?>'.preg_replace("/;*\s*\?>/", '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
        } else {
            include $_ci_path; // include() vs include_once() allows for multiple views with the same name
        }

        log_message('debug', 'File loaded: '.$_ci_path);

        // Return the file data if requested
        if ($_ci_return === true) {
            $buffer = ob_get_contents();
            @ob_end_clean();

            return $buffer;
        }

        /*
         * Flush the buffer... or buff the flusher?
         *
         * In order to permit views to be nested within
         * other views, we need to flush the content back out whenever
         * we are beyond the first level of output buffering so that
         * it can be seen and included properly by the first included
         * template and any subsequent ones. Oy!
         *
         */
        if (ob_get_level() > $this->_ci_ob_level + 1) {
            ob_end_flush();
        } else {
            $_ci_CI->output->append_output(ob_get_contents());
            @ob_end_clean();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Load class.
     *
     * This function loads the requested class.
     *
     * @param	string	the item that is being loaded
     * @param	mixed	any additional parameters
     * @param	string	an optional object name
     *
     * @return void
     */
    public function _ci_load_class($class, $params = null, $object_name = null)
    {
        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace(EXT, '', trim($class, '/'));

        // Was the path included with the class name?
        // We look for a slash to determine this
        $subdir = '';
        if (($last_slash = strrpos($class, '/')) !== false) {
            // Extract the path
            $subdir = substr($class, 0, $last_slash + 1);

            // Get the filename from the path
            $class = substr($class, $last_slash + 1);
        }

        // We'll test for both lowercase and capitalized versions of the file name
        foreach ([ucfirst($class), strtolower($class)] as $class) {
            $subclass = APPPATH.'libraries/'.$subdir.config_item('subclass_prefix').$class.EXT;

            // Is this a class extension request?
            if (file_exists($subclass)) {
                $baseclass = BASEPATH.'libraries/'.ucfirst($class).EXT;

                if (!file_exists($baseclass)) {
                    log_message('error', 'Unable to load the requested class: '.$class);
                    show_error('Unable to load the requested class: '.$class);
                }

                // Safety:  Was the class already loaded by a previous call?
                if (in_array($subclass, $this->_ci_loaded_files)) {
                    // Before we deem this to be a duplicate request, let's see
                    // if a custom object name is being supplied.  If so, we'll
                    // return a new instance of the object
                    if (!is_null($object_name)) {
                        $CI = &get_instance();
                        if (!isset($CI->$object_name)) {
                            return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
                        }
                    }

                    $is_duplicate = true;
                    log_message('debug', $class.' class already loaded. Second attempt ignored.');

                    return;
                }

                include_once $baseclass;
                include_once $subclass;
                $this->_ci_loaded_files[] = $subclass;

                return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
            }

            // Lets search for the requested library file and load it.
            $is_duplicate = false;
            foreach ($this->_ci_library_paths as $path) {
                $filepath = $path.'libraries/'.$subdir.$class.EXT;

                // Does the file exist?  No?  Bummer...
                if (!file_exists($filepath)) {
                    continue;
                }

                // Safety:  Was the class already loaded by a previous call?
                if (in_array($filepath, $this->_ci_loaded_files)) {
                    // Before we deem this to be a duplicate request, let's see
                    // if a custom object name is being supplied.  If so, we'll
                    // return a new instance of the object
                    if (!is_null($object_name)) {
                        $CI = &get_instance();
                        if (!isset($CI->$object_name)) {
                            return $this->_ci_init_class($class, '', $params, $object_name);
                        }
                    }

                    $is_duplicate = true;
                    log_message('debug', $class.' class already loaded. Second attempt ignored.');

                    return;
                }

                include_once $filepath;
                $this->_ci_loaded_files[] = $filepath;

                return $this->_ci_init_class($class, '', $params, $object_name);
            }
        } // END FOREACH

        // One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
        if ($subdir == '') {
            $path = strtolower($class).'/'.$class;

            return $this->_ci_load_class($path, $params);
        }

        // If we got this far we were unable to find the requested class.
        // We do not issue errors if the load call failed due to a duplicate request
        if ($is_duplicate == false) {
            log_message('error', 'Unable to load the requested class: '.$class);
            show_error('Unable to load the requested class: '.$class);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Instantiates a class.
     *
     * @param	string
     * @param	string
     * @param	string	an optional object name
     *
     * @return null
     */
    public function _ci_init_class($class, $prefix = '', $config = false, $object_name = null)
    {
        // Is there an associated config file for this class?  Note: these should always be lowercase
        if ($config === null) {
            // Fetch the config paths containing any package paths
            $config_component = $this->_ci_get_component('config');

            if (is_array($config_component->_config_paths)) {
                // Break on the first found file, thus package files
                // are not overridden by default paths
                foreach ($config_component->_config_paths as $path) {
                    // We test for both uppercase and lowercase, for servers that
                    // are case-sensitive with regard to file names. Check for environment
                    // first, global next
                    if (file_exists($path.'config/'.ENVIRONMENT.'/'.strtolower($class).EXT)) {
                        include_once $path.'config/'.ENVIRONMENT.'/'.strtolower($class).EXT;
                        break;
                    } elseif (file_exists($path.'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).EXT)) {
                        include_once $path.'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).EXT;
                        break;
                    } elseif (file_exists($path.'config/'.strtolower($class).EXT)) {
                        include_once $path.'config/'.strtolower($class).EXT;
                        break;
                    } elseif (file_exists($path.'config/'.ucfirst(strtolower($class)).EXT)) {
                        include_once $path.'config/'.ucfirst(strtolower($class)).EXT;
                        break;
                    }
                }
            }
        }

        if ($prefix == '') {
            if (class_exists('CI_'.$class)) {
                $name = 'CI_'.$class;
            } elseif (class_exists(config_item('subclass_prefix').$class)) {
                $name = config_item('subclass_prefix').$class;
            } else {
                $name = $class;
            }
        } else {
            $name = $prefix.$class;
        }

        // Is the class name valid?
        if (!class_exists($name)) {
            log_message('error', 'Non-existent class: '.$name);
            show_error('Non-existent class: '.$class);
        }

        // Set the variable name we will assign the class to
        // Was a custom class name supplied?  If so we'll use it
        $class = strtolower($class);

        if (is_null($object_name)) {
            $classvar = (!isset($this->_ci_varmap[$class])) ? $class : $this->_ci_varmap[$class];
        } else {
            $classvar = $object_name;
        }

        // Save the class name and object name
        $this->_ci_classes[$class] = $classvar;

        // Instantiate the class
        $CI = &get_instance();
        if ($config !== null) {
            $CI->$classvar = new $name($config);
        } else {
            $CI->$classvar = new $name();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Autoloader.
     *
     * The config/autoload.php file contains an array that permits sub-systems,
     * libraries, and helpers to be loaded automatically.
     *
     * @param	array
     *
     * @return void
     */
    public function _ci_autoloader()
    {
        include_once APPPATH.'config/autoload'.EXT;

        if (!isset($autoload)) {
            return false;
        }

        // Autoload packages
        if (isset($autoload['packages'])) {
            foreach ($autoload['packages'] as $package_path) {
                $this->add_package_path($package_path);
            }
        }

        // Load any custom config file
        if (count($autoload['config']) > 0) {
            $CI = &get_instance();
            foreach ($autoload['config'] as $key => $val) {
                $CI->config->load($val);
            }
        }

        // Autoload helpers and languages
        foreach (['helper', 'language'] as $type) {
            if (isset($autoload[$type]) and count($autoload[$type]) > 0) {
                $this->$type($autoload[$type]);
            }
        }

        // A little tweak to remain backward compatible
        // The $autoload['core'] item was deprecated
        if (!isset($autoload['libraries']) and isset($autoload['core'])) {
            $autoload['libraries'] = $autoload['core'];
        }

        // Load libraries
        if (isset($autoload['libraries']) and count($autoload['libraries']) > 0) {
            // Load the database driver.
            if (in_array('database', $autoload['libraries'])) {
                $this->database();
                $autoload['libraries'] = array_diff($autoload['libraries'], ['database']);
            }

            // Load all other libraries
            foreach ($autoload['libraries'] as $item) {
                $this->library($item);
            }
        }

        // Autoload models
        if (isset($autoload['model'])) {
            $this->model($autoload['model']);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Object to Array.
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param	object
     *
     * @return array
     */
    public function _ci_object_to_array($object)
    {
        return (is_object($object)) ? get_object_vars($object) : $object;
    }

    // --------------------------------------------------------------------

    /**
     * Get a reference to a specific library or model.
     *
     * @return bool
     */
    public function &_ci_get_component($component)
    {
        $CI = &get_instance();

        return $CI->$component;
    }

    // --------------------------------------------------------------------

    /**
     * Prep filename.
     *
     * This function preps the name of various items to make loading them more reliable.
     *
     * @param	mixed
     *
     * @return array
     */
    public function _ci_prep_filename($filename, $extension)
    {
        if (!is_array($filename)) {
            return [strtolower(str_replace(EXT, '', str_replace($extension, '', $filename)).$extension)];
        } else {
            foreach ($filename as $key => $val) {
                $filename[$key] = strtolower(str_replace(EXT, '', str_replace($extension, '', $val)).$extension);
            }

            return $filename;
        }
    }
}

/* End of file Loader.php */
/* Location: ./system/core/Loader.php */
