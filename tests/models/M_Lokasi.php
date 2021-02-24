<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Lokasi extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function getDataLokasi()
	{
		$this->db->select("id_lokasi, nama_lokasi");
        $this->db->from("tb_lokasi");
        $query = $this->db->get();
        
        return $query->result_array();
	}

	public function editLokasi($id_lokasi,$nama_lokasi)
	{
		$updated = false;

		$this->db->where('id_lokasi',$id_lokasi);
		$this->db->update('tb_lokasi', array(
			'nama_lokasi'=>$nama_lokasi
		));		

		if ($this->db->affected_rows() > 0)
		{
			$updated = true;
		}

		return $updated;
	}

	public function deleteLokasi($id_lokasi)
	{
		$deleted = false;

		$this->db->where('id_lokasi',$id_lokasi);
		$this->db->delete('tb_lokasi');		

		if($this->db->affected_rows() > 0)
		{
			$deleted = true;
		}

		return $deleted;
	}

	public function createLokasi($nama_lokasi)
	{
		$inserted = false;

		$this->db->insert('tb_lokasi', array(
			'nama_lokasi'=>$nama_lokasi
		));

		if ($this->db->affected_rows() > 0)
		{
			$inserted = TRUE;
		}

		return $inserted;
	}
}
