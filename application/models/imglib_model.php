<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Imglib_model extends CI_Model
{
    public $img_name = '';
    public $img_name_thumb = '';
    public $img_title = '';
    public $img_desc = '';
    public $img_file_type = '';
    public $img_file_size = '';

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function add_image()
    {
        $this->db->set('img_name', $this->img_name);
        $this->db->set('img_name_thumb', $this->img_name_thumb);
        $this->db->set('img_file_type', $this->img_file_type);
        $this->db->set('img_file_size', $this->img_file_size);
        $this->db->insert('img_lib');
    }

    public function get_by_thumb($img_name_thumb)
    {
        $this->db->where('img_name_thumb', $img_name_thumb);
        $query = $this->db->get('img_lib');

        return $query->row();
    }
}
