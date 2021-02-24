<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Tabungan extends CI_Model
{
    public static $STATUS_PENDING = 1;
    public static $STATUS_APPROVE = 2;
    public static $STATUS_REJECT = 3;

    public static $TIPE_NABUNG = 1;
    public static $TIPE_TARIK_TUNAI = 2;

    public function getTabunganData($tabunganNo = null)
    {
        if ($tabunganNo === null) {
            return $this->db->get_where('tabungan', ['status' => $this->tabungan::$STATUS_APPROVE, 'type' => $this->tabungan::$TIPE_NABUNG ])->result_array();
        } else {
            return $this->db->get_where('tabungan', ['tabunganNo' => $tabunganNo])->result_array();
        }
    }

    public function getTabunganUsername($username = null, $limit = null)
    {
        $this->db->select('*');
        $this->db->from('tabungan');
        $this->db->where('username', $username);
        $this->db->order_by('tabunganNo', 'desc');
        $this->db->limit($limit , $limit - 10);
        

        $query = $this->db->get();
		return $query->result_array();
    }

    public function countTabungan($username = null)
    {
        // $this->db->select('username, SUM(total) as total', FALSE);
        // $this->db->from('tabungan');
        // $this->db->where('username', $username);
        // $this->db->where('status', $this->tabungan::$STATUS_APPROVE);
        // $this->db->group_by("username");
        
        // $query = $this->db->get();
        // return $query->result_array();

        $status = $this->tabungan::$STATUS_APPROVE;
        $sql = "select username, 
                IFNULL(SUM(total), 0)  as totalTabungan,
                (
                    select  IFNULL(SUM(total), 0) 
                    from tabungan 
                    where username = '$username' and type = 2 and status = '$status'
                   
                ) as totalTarik,
                (
                    select  IFNULL(SUM(total), 0) 
                    from transaction_head 
                    where username = '$username' and status = '$status'
                   
                ) as totalTransaksi
                from tabungan 
                where username = '$username' and type = 1 and status = '$status'";    
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getTabunganAdmin($limit = null)
    {
        $this->db->select('a.*, b.*');
        $this->db->from('tabungan a');
        $this->db->join('user_detail b', 'b.username = a.username', 'left');
        $this->db->where('status', $this->tabungan::$STATUS_PENDING);
        $this->db->where('type', $this->tabungan::$TIPE_NABUNG);
        $this->db->order_by('tabunganNo', 'desc');
        $this->db->limit($limit , $limit - 10);
        

        $query = $this->db->get();
		return $query->result_array();
    }

    public function getTransaksiAdmin($limit = null)
    {
        // $this->db->select('a.*, b.*');
        // $this->db->from('tabungan a');
        // $this->db->join('user_detail b', 'b.username = a.username', 'left');
        // $this->db->where('status', $this->tabungan::$STATUS_PENDING);
        // $this->db->where('type', $this->tabungan::$TIPE_TARIK_TUNAI);
        // $this->db->order_by('tabunganNo', 'desc');
        // $this->db->limit($limit , $limit - 10);
        

        // $query = $this->db->get();
        // return $query->result_array();
        
        $statusTabungan = $this->tabungan::$STATUS_PENDING;
        $limits = $limit - 10;
        $type = $this->tabungan::$TIPE_TARIK_TUNAI;
        $sql = "select id, nama, transactionNum as tabunganNo, transaction_head.username, total, 3 as type, status, transaction_head.createDate 
                FROM transaction_head
                join user_detail on user_detail.username = transaction_head.username
                where status = '$statusTabungan'
                UNION
                select id, nama, tabunganNo, tabungan.username, total, type, status, tabungan.createDate
                FROM tabungan
                join user_detail on user_detail.username = tabungan.username
                where type = '$type' and status = '$statusTabungan'
                order By createDate DESC
                limit  $limits, $limit";    
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getHistoryAdmin($limit = null)
    {
        // $this->db->select('a.*, b.*');
        // $this->db->from('tabungan a');
        // $this->db->join('user_detail b', 'b.username = a.username', 'left');
        // $this->db->where('status', $this->tabungan::$STATUS_PENDING);
        // $this->db->where('type', $this->tabungan::$TIPE_TARIK_TUNAI);
        // $this->db->order_by('tabunganNo', 'desc');
        // $this->db->limit($limit , $limit - 10);
        

        // $query = $this->db->get();
        // return $query->result_array();
        
        $statusTabungan = $this->tabungan::$STATUS_PENDING;
        $limits = $limit - 10;
        $type_1 = $this->tabungan::$TIPE_TARIK_TUNAI;
        $type_2 = $this->tabungan::$TIPE_NABUNG;
        $sql = "select id, nama, transactionNum as tabunganNo, transaction_head.username, total, 3 as type, 'transaksi' as typeName, status, transaction_head.createDate 
                FROM transaction_head
                join user_detail on user_detail.username = transaction_head.username
                UNION
                select id, nama, tabunganNo, tabungan.username, total, type, 'nabung' as typeName, status, tabungan.createDate
                FROM tabungan
                join user_detail on user_detail.username = tabungan.username
                where type = '$type_2'
                UNION
                select id, nama, tabunganNo, tabungan.username, total, type, 'tarik tunai' as typeName, status, tabungan.createDate
                FROM tabungan
                join user_detail on user_detail.username = tabungan.username
                where type = '$type_1'
                order By createDate DESC
                limit  $limits, $limit";    
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getHistoryUser($limit = null, $username = null)
    {
        // $this->db->select('a.*, b.*');
        // $this->db->from('tabungan a');
        // $this->db->join('user_detail b', 'b.username = a.username', 'left');
        // $this->db->where('status', $this->tabungan::$STATUS_PENDING);
        // $this->db->where('type', $this->tabungan::$TIPE_TARIK_TUNAI);
        // $this->db->order_by('tabunganNo', 'desc');
        // $this->db->limit($limit , $limit - 10);
        

        // $query = $this->db->get();
        // return $query->result_array();
        
        $statusTabungan = $this->tabungan::$STATUS_PENDING;
        $limits = $limit - 10;
        $type_1 = $this->tabungan::$TIPE_TARIK_TUNAI;
        $type_2 = $this->tabungan::$TIPE_NABUNG;
        $sql = "select id, nama, transactionNum as tabunganNo, transaction_head.username, total, 3 as type, 'transaksi' as typeName, status, transaction_head.createDate 
                FROM transaction_head
                join user_detail on user_detail.username = transaction_head.username
                where transaction_head.username = '$username'
                UNION
                select id, nama, tabunganNo, tabungan.username, total, type, 'nabung' as typeName, status, tabungan.createDate
                FROM tabungan
                join user_detail on user_detail.username = tabungan.username
                where type = '$type_2' AND tabungan.username = '$username'
                UNION
                select id, nama, tabunganNo, tabungan.username, total, type, 'tarik tunai' as typeName, status, tabungan.createDate
                FROM tabungan
                join user_detail on user_detail.username = tabungan.username
                where type = '$type_1' AND tabungan.username = '$username'
                order By createDate DESC
                limit  $limits, $limit";    
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function countTabunganAdmin()
    {
        // $this->db->select('SUM(total) as total');
        // $this->db->from('tabungan');
        // $this->db->where('status', $this->tabungan::$STATUS_APPROVE);
        // $this->db->where('type', $this->tabungan::$TIPE_NABUNG);
        
        // $query = $this->db->get();
        // return $query->result_array();
        
        $status = $this->tabungan::$STATUS_APPROVE;
        $sql = "select username, 
                IFNULL(SUM(total), 0)  as totalTabungan,
                (
                    select  IFNULL(SUM(total), 0) 
                    from tabungan 
                    where type = 2 and status = '$status'
                ) as totalTarik,
                (
                    select  IFNULL(SUM(total), 0) 
                    from transaction_head 
                    where status = '$status'
                   
                ) as totalTransaksi
                from tabungan 
                where  type = 1 and status = '$status'";    
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function deleteTabungan($id)
    {
        $this->db->delete('tabungan', ['tabunganNo' => $id]);
        return $this->db->affected_rows();
    }

    public function createDataTabungan($data)
    {
        $this->db->insert('tabungan', $data);
        return $this->db->affected_rows();
    }

    public function getMaxbyDate($prefix = null, $table = null, $field = null)
    {
        $this->db->select('tabunganNo');
        $this->db->like($field, $prefix, 'after');
        $this->db->order_by($field, 'desc');
        $this->db->limit(1);

        return $this->db->get($table)->row_array()[$field];
    }

    public function updateTabungan($data, $update)
    {
        $this->db->update('tabungan', $data, ['tabunganNo' => $update]);
        return $this->db->affected_rows();
    }

    public function updateApprove($data, $update)
    {
        $this->db->update('tabungan', $data, ['tabunganNo' => $update]);
        return $this->db->affected_rows();
    }

    public function updateReject($data, $update)
    {
        $this->db->update('tabungan', $data, ['tabunganNo' => $update]);
        return $this->db->affected_rows();
    }
}
