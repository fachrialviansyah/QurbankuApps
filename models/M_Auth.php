<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Auth extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function checkUser($username, $password)
	{
		$usernames = $username;
		$passwords = md5($password);

		$this->db->select("a.username, b.nama, a.user_role, b.id");
		$this->db->from("user a");
		$this->db->join('user_detail b', 'b.username = a.username', 'left');
		$this->db->where('a.username', $usernames);
		$this->db->where('a.password', $passwords);
		$this->db->where('a.status', 1);
		$query = $this->db->get();

		return $query->row();
	}

	public function updateAuth($authKey, $username)
	{
		$updated = false;

		$this->db->where('username', $username);
		$this->db->update('user', array(
			'authkey' => $authKey
		));

		if ($this->db->affected_rows() > 0) {
			$updated = true;
		}

		return $updated;
	}

	public function getAuth($auth)
	{
		$this->db->select("authkey");
		$this->db->from("user");
		$this->db->where('authkey', $auth);
		$query = $this->db->get();

		return $query->row();
	}

	public function isValidToken($username)
	{
		$this->db->select("username");
		$this->db->from("user");
		$this->db->where("username", $username);
		$query = $this->db->get();

		return $query->num_rows();
	}
}
