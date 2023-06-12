<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Global_model extends CI_Model
{
    public $username = '';
    public $password = '';
    public $last_login = '';
    public $nama_lengkap = '';

    public $add_on_id = '';
    public $add_on_name = '';
    public $add_on_def_controller = '';
    public $add_on_def_setting = '';
    public $js_script_generated = '';

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function get_my_profile($user_id)
    {
        $query = $this->db->get_where('user', ['user_id'=>$user_id]);

        return $query->row();
    }

    public function update_profile($user_id)
    {
        $this->db->set('username', $this->username);
        if ($this->password != '') {
            $this->db->set('password', $this->password);
        }
        $this->db->set('nama_lengkap', $this->nama_lengkap);
        $this->db->where('user_id', $user_id);
        $this->db->update('user');
    }

    public function update_skin($site_skin)
    {
        $this->db->set('site_skin', $site_skin);
        $this->db->update('site_config');
    }

    public function cek_module($add_on_id)
    {
        $this->db->where('add_on_id', $add_on_id);
        $query = $this->db->get('add_on');

        return $query->num_rows;
    }

    public function get_module_detail($add_on_id)
    {
        $this->db->where('add_on_id', $add_on_id);
        $query = $this->db->get('add_on');

        return $query;
    }

    public function add_module()
    {
        $this->db->set('add_on_id', $this->add_on_id);
        $this->db->set('add_on_name', $this->add_on_name);
        $this->db->set('add_on_def_controller', $this->add_on_def_controller);
        $this->db->set('add_on_def_setting', $this->add_on_def_setting);
        $this->db->insert('add_on');
    }

    public function get_module()
    {
        $query = $this->db->get('add_on');

        return $query;
    }

    public function delete_module($add_on_id)
    {
        $this->db->where('add_on_id', $add_on_id);
        $this->db->delete('add_on');
    }

    public function update_module($add_on_id)
    {
        $this->db->set('add_on_def_setting', $this->add_on_def_setting);
        $this->db->set('js_script_generated', $this->global_model->js_script_generated);
        $this->db->where('add_on_id', $add_on_id);
        $this->db->update('add_on');
    }
}
