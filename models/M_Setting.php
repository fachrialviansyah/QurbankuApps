<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Setting extends CI_Model
{
    public function getSetting($condition = null)
    {
        $this->db->select('*');
        $this->db->from('setting');
        $this->db->where('key', $condition);
        
        $query = $this->db->get();
		return $query->result_array();
    }

    public function updateSetting($data, $key)
    {
        $this->db->update('setting', $data, ['key' => $key]);
        return $this->db->affected_rows();
    }
}
