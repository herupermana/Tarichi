<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Flash_item_model extends CI_Model
{
    public $flash_item_title = '';
    public $flash_item_desc = '';
    public $flash_item_image = '';
    public $flash_item_link_to = '';
    public $flash_item_order = '';

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function get_all()
    {
        $this->db->order_by('flash_item_order');
        $query = $this->db->get('flash_item');

        return $query->result();
    }
}
