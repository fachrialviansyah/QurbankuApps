<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once FCPATH . 'vendor/autoload.php';

use \Firebase\JWT\JWT;

class Auth extends REST_Controller
{

	private $secretkey = 'c0Mb1nAtioNTravel';

	public function __construct()
	{
		header('Access-Control-Allow-Origin: *');
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('M_Auth');
	}

	//method untuk not found 404
	public function notfound($pesan)
	{
		$this->response([
			'status' => FALSE,
			'message' => $pesan
		], REST_Controller::HTTP_NOT_FOUND);
	}

	//method untuk bad request 400
	public function badreq($pesan)
	{
		$this->response([
			'status' => FALSE,
			'message' => $pesan
		], REST_Controller::HTTP_BAD_REQUEST);
	}

	//method untuk jika view token diatas fail
	public function loginFailed($username, $password, $error)
	{
		$this->response([
			'status' => FALSE,
			'username' => $username,
			'password' => $password,
			'message' => $error
		], REST_Controller::HTTP_OK);
	}

	//method untuk melihat token pada user
	public function login_post()
	{
		$date = new DateTime();
		$username = $this->post('username', TRUE);
		$password = $this->post('password', TRUE);

		$data_login = $this->M_Auth->checkUser($username, $password);

		if ($data_login) {
			$payload['data_user'] = $data_login;
			$payload['iat'] = $date->getTimestamp(); //waktu di buat
			$payload['exp'] = $date->getTimestamp() + 2629746; //satu bulan

			$token = JWT::encode($payload, $this->secretkey); //fungsi untuk Get Token
			$this->M_Auth->updateAuth($token, $data_login->username);
			$this->response([
				'status' => TRUE,
				'fullName' =>  $data_login->nama,
				'userName' =>  $data_login->username,
				'userRole' =>  $data_login->user_role,
				'userID' =>  $data_login->id,
				'token' => $token
			], REST_Controller::HTTP_OK);
		} else {
			$error = "User not found";
			$this->loginFailed($username, $password, $error);
		}
	}

	//method untuk mengecek token setiap melakukan post, put, etc
	public function checkToken()
	{
		$jwt = $this->input->get_request_header('Authorization');
		$get_auth = $this->M_Auth->getAuth($jwt);
		if ($get_auth) {
			try {
				$decode = JWT::decode($jwt, $this->secretkey, array('HS256'));
				$username = $decode->data_user->username;

				//melakukan pengecekan database, jika nama tersedia di database maka return true
				if ($this->M_Auth->isValidToken($username) > 0) {
					$res['username'] = $username;

					return $res;
				}
			} catch (Exception $e) {
				exit('Wrong Token');
			}
		} else {
			return false;
		}
	}
}
