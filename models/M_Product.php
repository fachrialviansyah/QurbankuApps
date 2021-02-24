<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Product extends CI_Model
{
    public function getProduct($limit = null)
    {
        $this->db->select('*');
        $this->db->from('product');
        $this->db->order_by('productName', 'asc');
        $this->db->limit($limit , $limit - 10);
        

        $query = $this->db->get();
		return $query->result_array();
    }
}
