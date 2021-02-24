<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . 'controllers/api/Auth.php';

class Product extends Auth
{
    public $fcmKey = 'AAAAR1m1fPI:APA91bEmVS6oUY_TTmG4UV_V7JJc6DRaecyv7603wnMuupXqjkFMeyCZGEV472bNk_0KwBGHXV2C4m0CoTK2HDv0amEFGA76_q1i53wUY1zpLhLZJCTvFM_Y-Yi2dszKGCEv-79wrMTc';

    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization');
        parent::__construct();
        $this->load->model('M_Product', 'product');
        $this->load->helper(array('form', 'url'));
        $this->data_global = $this->checkToken();
        $this->load->library('upload');
    }

    function failed($pesan)
    {
        $this->response([
			'status' => FALSE,
			'message' => $pesan
		], REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function dataProduct_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $limit = $this->post('limit');
            $product = $this->product->getProduct($limit);

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
}
