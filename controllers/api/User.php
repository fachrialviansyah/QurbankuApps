<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Auth.php';

class User extends Auth
{
	public function __construct()
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: Authorization');
		parent::__construct();
		$this->load->model('M_User');
		//mengecek token pada class Restdata, di mana jika token invalid maka akan melakukan exit
		$this->data_global = $this->checkToken();
	}

	function allUser_get()
	{
		if (!$this->data_global) {
			$this->failed("Anda tidak memiliki akses!");
		} else {
			$get_data = $this->M_User->getAllUser();

			if ($get_data) {
				$res['status'] = TRUE;
				$res['data'] = $get_data;
				$this->response($res, Auth::HTTP_OK);
			} else {
				$this->failed("Not Found");
			}
		}
	}

	public function allUsers_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $limit = $this->post('limit');
            $user = $this->M_User->getAllUsers($limit);

            if ($user) {
                $this->response([
                    'status' => true,
                    'data' => $user,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
	}
	
	public function userDetail_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $username = $this->post('username');
            $user = $this->M_User->getUsers($username);

            if ($user) {
                $this->response([
                    'status' => true,
                    'data' => $user,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

	function createAccount_post()
	{
		#Proses untuk save data ke tabel user & user_detail
		#bkin model pengecekannya dsni
		$data = array(
			'username' => $this->post('username', TRUE),
			'nama' => $this->post('nama', TRUE),
		);
		$modelCheck = $this->M_User->checkUser($data['username']);

		if (!$modelCheck) {
			$model = $this->M_User->createAccount($data); #proses query inset data
			$res['status'] = $model;
			$res['message'] = 'Email berhasil didaftarkan, Silahkan cek email anda untuk verifikasi.';
			$this->response($res, Auth::HTTP_OK);
		} else {
			$this->failed('Email Sudah Terdaftar');
		}
	}

	function editPassword_post()
	{
		#fungsi untuk edit password
		$username = $this->input->post('username');
		$data = array(
			'password' => md5($this->post('password', TRUE))
		);
		$model = $this->M_User->editPassword($data, $username); #proses query inset data
		$res['status'] = $model;
		$res['message'] = 'Password berhasil diubah';
		$res['data'] = $model;
		$this->response($res, Auth::HTTP_OK);
	}

	function failed($pesan)
	{
		$res['status'] = FALSE;
		$res['message'] = $pesan;
		$this->response($res, Auth::HTTP_OK);
	}

	function updateUserDetail_put()
	{
		$username = $this->input->update('username');
		$alamat = $this->input->update('alamat');
		$data = array(
			'nama' => $this->put('nama', TRUE),
			'NIK' => $this->put('NIK', TRUE),
			'JK' => $this->put('JK', TRUE),
			'alamat' => $this->put('alamat', TRUE),
			'noTlp' => $this->put('noTlp', TRUE),
			'bankId' => $this->put('bankId', TRUE),
			'fotoKtp' => $this->put('fotoKtp', TRUE),
			'fotoTabungan' => $this->put('fotoTabungan', TRUE),
		);

		$alamat = $this->M_User->editUserDetail($data, $username);
		// $model = $this->M_User->editPassword($data, $username);
		$res['status'] = TRUE;
		$res['data'] = $data;
		$this->response($res, Auth::HTTP_OK);
	}

	public function updateUserDetail_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
			$username = $this->input->post("username");
            $image_ktp = base64_decode($this->input->post("imagesKTP"));
            $filename_ktp = 'KTP_'. $username . '.' . 'jpg';

            $pathKtp = "uploads/ktp/" . $filename_ktp;
            //image uploading folder path
			file_put_contents($pathKtp, $image_ktp);
			

			$image_rek = base64_decode($this->input->post("imagesREK"));
            $filename_rek = 'REK_'. $username . '.' . 'jpg';

            $pathRek = "uploads/buku-tabungan/" . $filename_rek;
            //image uploading folder path
            file_put_contents($pathRek, $image_rek);
           
            $data = [
                'fotoKtp' => $filename_ktp,
                'fotoTabungan' => $filename_rek,
                'NIK' => $this->post('NIK'),
                'noRek' => $this->post('REK')
            ];

            if ($this->M_User->editUserDetail($data, $username) > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'Data Berhasil di Input',
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Gagal masukan data'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }
}
