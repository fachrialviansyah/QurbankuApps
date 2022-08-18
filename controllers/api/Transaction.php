<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . 'controllers/api/Auth.php';

class Transaction extends Auth
{
    public $fcmKey = 'AAAAR1m1fPI:APA91bEmVS6oUY_TTmG4UV_V7JJc6DRaecyv7603wnMuupXqjkFMeyCZGEV472bNk_0KwBGHXV2C4m0CoTK2HDv0amEFGA76_q1i53wUY1zpLhLZJCTvFM_Y-Yi2dszKGCEv-79wrMTc';

    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization');
        parent::__construct();
        $this->load->model('M_User');
        $this->load->model('M_Tabungan', 'tabungan');
        $this->load->model('M_Transaction', 'transaction');
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

    public function createTransaction_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $transactionNum = $this->byDate_get();
            $hariIni = date("Y-m-d H:i:s");
            $total = str_replace(".", ",", str_replace(",", "", $this->post('total')));

            $data = [
                'transactionNum' => $transactionNum,
                'transactionDate' => $hariIni,
                'username' => $this->post('username'),
                'total' => $total,
                'status' => $this->transaction::$STATUS_PENDING,
                'createBy' => $this->post('username'),
                'createDate' => $hariIni,
                'updateBy' => $this->post('username'),
                'updateDate' => $hariIni
            ];


            $detail = $this->post('data');
            foreach ($detail as $key) {
                $subTotal = str_replace(".", ",", str_replace(",", "", $key['total']));
                $dataDetail = [
                    'transactionNum' => $transactionNum,
                    'productID' => $key['id'],
                    'subTotal' => $subTotal,
                ];
                $this->transaction->createDataTransactionDetail($dataDetail);
            }

            if ($this->transaction->createDataTransaction($data) > 0 ) {
                $arrayDataAdmin = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Transaksi masuk dengan nomor $transactionNum"
                    ],
                    "to" => "/topics/administrator"
                );

                $this->pushNotification($arrayDataAdmin);
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

    public function pushNotification($arrayData, $userID = null, $messageMail = null)
    {
        $ch = curl_init();
        $headers  = [
            "Authorization: key= $this->fcmKey",
            "Content-Type: application/json"
        ];

        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result     = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($userID) {
            $username =  $this->M_User->getUser($userID);
            #Proses Config kirim email
            $this->load->library('email'); //panggil library email codeigniter
            $config = array(
                'protocol' => 'smtp',
                'smtp_host' => 'ssl://smtp.googlemail.com',
                'smtp_port' => 465,
                'smtp_user' => 'qurbankuapps@gmail.com', //alamat email gmail
                'smtp_pass' => '**********', //password email 
                'mailtype' => 'html',
                'charset' => 'iso-8859-1',
                'wordwrap' => TRUE
            );

            // Content Massage
            $message = '<html><body>';
            $message .= '<img src="logo.jpg" width="500" height="600" alt="Qurbanku"/>';
            $message .= '<table align="center" border="1" cellpadding="0" cellspacing="0" width="600"';
            $message .= '<tr style="background-color:#5FEA56"><h2 style="text-align:center">Pemberitahuan</h2></tr>';
            $message .= "<tr><td><p style='color:black;'>$messageMail</b>.<br> ";
            $message .= '</body></html>';
            // "Terima kasih telah mendaftarkan diri Anda di <b>Aplikasi Qurbanku</b>.<br> Kode password verifikasi 
            // Anda: <b>$password</b><br>Harap jangan berikan kode password verifikasi
            // kepada orang lain & segera ganti password Anda!"; //ini adalah isi body email isi nya varibale password
            $email = $username[0]['username']; //email penerima isiya username


            $this->email->initialize($config);
            $this->email->set_newline("\r\n");
            $this->email->from($config['smtp_user']);
            $this->email->to($email);
            $this->email->subject('Pemberitahuan !'); //subjek email
            $this->email->message($message);
            $this->email->send();
        }
    }

    public function byDate_get()
    {
        $table = "transaction_head";
        $field = "transactionNum";

        //fungsi tanggal
        $today = date('ymd');

        //menampilkan TR201107
        $prefix = "TR" . $today;

        $lastKode = $this->transaction->getMaxbyDate($prefix, $table, $field);

        // mengambil 4 karakter dari belakang
        $noUrut = (int) substr($lastKode, -4, 4);
        $noUrut++;

        $newKode = $prefix . sprintf('%04s', $noUrut);

        return $newKode;
        // var_dump($newKode);
    }
}
