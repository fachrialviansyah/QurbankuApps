<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_User extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function getAllUser()
	{
		$this->db->select("username, status");
		$this->db->from("user");
		$query = $this->db->get();

		return $query->result_array();
	}

	public function getAllusers($limit = null)
	{
		$this->db->select('a.*, b.*');
		$this->db->from('user a');
		$this->db->join('user_detail b', 'b.username = a.username');
		$this->db->where('a.user_role', 1);
		$this->db->order_by('b.nama', 'asc');
		$this->db->limit($limit , $limit - 10);
		

		$query = $this->db->get();
		return $query->result_array();
	}

	public function getUser($userID = null)
    {
		return $this->db->get_where('user_detail', ['id' => $userID])->result_array();
	}
	
	public function getUsers($username = null)
    {
		return $this->db->get_where('user_detail', ['username' => $username])->result_array();
    }

	public function createAccount($data)
	{
		$this->db->trans_begin();
		try {
			$password = mt_rand(100000, 999999);
			$username = $data['username'];
			$userRole = 1;
			$modelUser = $this->db->insert('user', array(
				'username' => $data['username'],
				'password' => md5($password), #passwordnya
				'user_role' => $userRole,
				'status' => 1 #angka kalau 1 user baru sign kalau 2 user sudah isi kelengkapan data
			));

			if (!$modelUser) {
				$this->db->trans_rollback();
				throw new Exception($modelUser);
			}

			$modelUserDetail = $this->db->insert('user_detail', array(
				'username' => $data['username'],
				'nama' => $data['nama']
			));

			if (!$modelUserDetail) {
				$this->db->trans_rollback();
				throw new Exception($modelUserDetail);
			}

			#Proses Config kirim email
			$this->load->library('email'); //panggil library email codeigniter
			$config = array(
				'protocol' => 'smtp',
				'smtp_host' => 'ssl://smtp.googlemail.com',
				'smtp_port' => 465,
				'smtp_user' => 'qurbankuapps@gmail.com', //alamat email gmail
				'smtp_pass' => '********', //password email 
				'mailtype' => 'html',
				'charset' => 'iso-8859-1',
				'wordwrap' => TRUE
			);

			// Content Massage
			$message = '<html><body>';
			$message .= '<img src="https://scontent.fcgk27-1.fna.fbcdn.net/v/t1.0-9/127182060_4719318924806241_2959586845410560513_n.jpg?_nc_cat=111&ccb=3&_nc_sid=730e14&_nc_eui2=AeEoaKUZYa2FdXLnW0kT9qxYi4hAJ5kU39iLiEAnmRTf2Mf3tJwWob-es9bebEQyOXSJKe8EuQUKfYN4dXOa9R3f&_nc_ohc=zlvdJ9Jo6r8AX-3Qh-x&_nc_oc=AQntGTmySDL1Nk2EoiHMIgg8pVMiha8wWzpJZqWoDd90cnNsltozt0aGBbEjP77nMj4&_nc_ht=scontent.fcgk27-1.fna&oh=bc27cd04ddc0048eb83aec73f445e4bd&oe=60549D63" width="100" height="100" style="vertical-align:middle;margin:center" alt="Qurbanku"/>';
			$message .= '<table align="center" border="1" cellpadding="0" cellspacing="0" width="600"';
			$message .= '<tr style="background-color:#5FEA56"><h2 style="text-align:center">Kode Verifikasi Qurbanku</h2></tr>';
			$message .= "<tr><td><p style='color:black;'>Terima kasih telah mendaftarkan diri Anda di <b>Aplikasi Qurbanku</b>.<br> Kode password verifikasi 
			Anda: <b>$password</b><br>Harap jangan berikan kode password verifikasi
			kepada orang lain & segera ganti password Anda!</p></td></tr>";
			$message .= '</body></html>';
			// "Terima kasih telah mendaftarkan diri Anda di <b>Aplikasi Qurbanku</b>.<br> Kode password verifikasi 
			// Anda: <b>$password</b><br>Harap jangan berikan kode password verifikasi
			// kepada orang lain & segera ganti password Anda!"; //ini adalah isi body email isi nya varibale password
			$email = $username; //email penerima isiya username


			$this->email->initialize($config);
			$this->email->set_newline("\r\n");
			$this->email->from($config['smtp_user']);
			$this->email->to($email);
			$this->email->subject('Verifikasi Qurbanku'); //subjek email
			$this->email->message($message);

			//proses kirim email
			if ($this->email->send()) {
				$result = true;
				$this->db->trans_commit();
			} else {
				$result = false;
				$this->db->trans_rollback();
			}
			return $result;
		} catch (Exception $e) {
			$this->db->trans_rollback();
			throw new Exception($e->getMessage());
			return false;
		}
	}

	public function checkUser($data)
	{
		$this->db->where('username', $data);
		$query = $this->db->get('user');
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function editPassword($data, $username)
	{
		$update = $this->db->where('username', $username)
			->set($data)
			->update('user');
		return $update;
	}

	public function editUserDetail($data, $username)
	{
		$update = $this->db->where('username', $username)
			->set($data)
			->update('user_detail');
		return $update;
	}
}
