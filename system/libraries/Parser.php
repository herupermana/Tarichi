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
 * Parser Class.
 *
 * @category	Parser
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/parser.html
 */
class CI_Parser
{
    public $l_delim = '{';
    public $r_delim = '}';
    public $object;

    /**
     *  Parse a template.
     *
     * Parses pseudo-variables contained in the specified template view,
     * replacing them with the data in the second param
     *
     * @param	string
     * @param	array
     * @param	bool
     *
     * @return string
     */
    public function parse($template, $data, $return = false)
    {
        $CI = &get_instance();
        $template = $CI->load->view($template, $data, true);

        return $this->_parse($template, $data, $return);
    }

    // --------------------------------------------------------------------

    /**
     *  Parse a String.
     *
     * Parses pseudo-variables contained in the specified string,
     * replacing them with the data in the second param
     *
     * @param	string
     * @param	array
     * @param	bool
     *
     * @return string
     */
    public function parse_string($template, $data, $return = false)
    {
        return $this->_parse($template, $data, $return);
    }

    // --------------------------------------------------------------------

    /**
     *  Parse a template.
     *
     * Parses pseudo-variables contained in the specified template,
     * replacing them with the data in the second param
     *
     * @param	string
     * @param	array
     * @param	bool
     *
     * @return string
     */
    public function _parse($template, $data, $return = false)
    {
        if ($template == '') {
            return false;
        }

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $template = $this->_parse_pair($key, $val, $template);
            } else {
                $template = $this->_parse_single($key, (string) $val, $template);
            }
        }

        if ($return == false) {
            $CI = &get_instance();
            $CI->output->append_output($template);
        }

        return $template;
    }

    // --------------------------------------------------------------------

    /**
     *  Set the left/right variable delimiters.
     *
     * @param	string
     * @param	string
     *
     * @return void
     */
    public function set_delimiters($l = '{', $r = '}')
    {
        $this->l_delim = $l;
        $this->r_delim = $r;
    }

    // --------------------------------------------------------------------

    /**
     *  Parse a single key/value.
     *
     * @param	string
     * @param	string
     * @param	string
     *
     * @return string
     */
    public function _parse_single($key, $val, $string)
    {
        return str_replace($this->l_delim.$key.$this->r_delim, $val, $string);
    }

    // --------------------------------------------------------------------

    /**
     *  Parse a tag pair.
     *
     * Parses tag pairs:  {some_tag} string... {/some_tag}
     *
     * @param	string
     * @param	array
     * @param	string
     *
     * @return string
     */
    public function _parse_pair($variable, $data, $string)
    {
        if (false === ($match = $this->_match_pair($string, $variable))) {
            return $string;
        }

        $str = '';
        foreach ($data as $row) {
            $temp = $match['1'];
            foreach ($row as $key => $val) {
                if (!is_array($val)) {
                    $temp = $this->_parse_single($key, $val, $temp);
                } else {
                    $temp = $this->_parse_pair($key, $val, $temp);
                }
            }

            $str .= $temp;
        }

        return str_replace($match['0'], $str, $string);
    }

    // --------------------------------------------------------------------

    /**
     *  Matches a variable pair.
     *
     * @param	string
     * @param	string
     *
     * @return mixed
     */
    public function _match_pair($string, $variable)
    {
        if (!preg_match('|'.preg_quote($this->l_delim).$variable.preg_quote($this->r_delim).'(.+?)'.preg_quote($this->l_delim).'/'.$variable.preg_quote($this->r_delim).'|s', $string, $match)) {
            return false;
        }

        return $match;
    }
}
// END Parser Class

/* End of file Parser.php */
/* Location: ./system/libraries/Parser.php */
