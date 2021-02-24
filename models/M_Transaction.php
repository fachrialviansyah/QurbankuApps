<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Transaction extends CI_Model
{
    public static $STATUS_PENDING = 1;
    public static $STATUS_APPROVE = 2;
    public static $STATUS_REJECT = 3;

    public function createDataTransaction($data)
    {
        $this->db->insert('transaction_head', $data);
        return $this->db->affected_rows();
    }

    public function createDataTransactionDetail($data)
    {
        $this->db->insert('transaction_detail', $data);
        return $this->db->affected_rows();
    }

    public function getMaxbyDate($prefix = null, $table = null, $field = null)
    {
        $this->db->select('transactionNum');
        $this->db->like($field, $prefix, 'after');
        $this->db->order_by($field, 'desc');
        $this->db->limit(1);

        return $this->db->get($table)->row_array()[$field];
    }

    public function updateApprove($data, $update)
    {
        $this->db->update('transaction_head', $data, ['transactionNum' => $update]);
        return $this->db->affected_rows();
    }

    public function updateReject($data, $update)
    {
        $this->db->update('transaction_head', $data, ['transactionNum' => $update]);
        return $this->db->affected_rows();
    }
}
