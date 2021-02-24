<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . 'controllers/api/Auth.php';

class Setting extends Auth
{
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization');
        parent::__construct();
        $this->load->model('M_Setting', 'setting');
        $this->load->helper(array('form', 'url'));
        $this->data_global = $this->checkToken();
    }

    function failed($pesan)
    {
        $this->response([
			'status' => FALSE,
			'message' => $pesan
		], REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function dataSetting_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $condition = $this->post('where');
            $product = $this->setting->getSetting($condition);

            if ($product) {
                $this->response([
                    'status' => true,
                    'data' => $product,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function updateSetting_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $key = $this->post('key');
            $data = [
                'value' => $this->post('value')
            ];

            if ($this->setting->updateSetting($data, $key) > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'Data Berhasil Diubah'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Gagal Diubah'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }
}
