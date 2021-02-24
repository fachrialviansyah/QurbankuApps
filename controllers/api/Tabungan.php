<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . 'controllers/api/Auth.php';

class Tabungan extends Auth
{
    public $fcmKey = 'AAAAR1m1fPI:APA91bEmVS6oUY_TTmG4UV_V7JJc6DRaecyv7603wnMuupXqjkFMeyCZGEV472bNk_0KwBGHXV2C4m0CoTK2HDv0amEFGA76_q1i53wUY1zpLhLZJCTvFM_Y-Yi2dszKGCEv-79wrMTc';

    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization');
        parent::__construct();
        $this->load->model('M_User');
        $this->load->model('M_Tabungan', 'tabungan');
        $this->load->model('M_Transaction', 'transaksi');
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

    public function dataTabungan_get()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $tabunganNo = $this->get('tabunganNo');
            if ($tabunganNo === null) {
                $tabungan = $this->tabungan->getTabunganData();
            } else {
                $tabungan = $this->tabungan->getTabunganData($tabunganNo);
            }

            if ($tabungan) {
                $this->response([
                    'status' => true,
                    'data' => $tabungan,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function dataTabunganUser_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $username = $this->post('username');
            $limit = $this->post('limit');
            $tabungan = $this->tabungan->getTabunganUsername($username, $limit);

            if ($tabungan) {
                $this->response([
                    'status' => true,
                    'data' => $tabungan,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function dataTabunganAdmin_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $limit = $this->post('limit');
            $tabungan = $this->tabungan->getTabunganAdmin($limit);

            if ($tabungan) {
                $this->response([
                    'status' => true,
                    'data' => $tabungan,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function dataTransaksiAdmin_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $limit = $this->post('limit');
            $tabungan = $this->tabungan->getTransaksiAdmin($limit);

            if ($tabungan) {
                $this->response([
                    'status' => true,
                    'data' => $tabungan,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function dataHistoryAdmin_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $limit = $this->post('limit');
            $tabungan = $this->tabungan->getHistoryAdmin($limit);

            if ($tabungan) {
                $this->response([
                    'status' => true,
                    'data' => $tabungan,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function dataHistoryUser_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $limit = $this->post('limit');
            $username = $this->post('username');
            $tabungan = $this->tabungan->getHistoryUser($limit, $username);

            if ($tabungan) {
                $this->response([
                    'status' => true,
                    'data' => $tabungan,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function deleteTabungan_delete()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $id = $this->delete('tabunganNo');
            if ($id === null) {
                $this->response([
                    'status' => false,
                    'massage' => 'Data Tabungan tidak ada.'
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                if ($this->tabungan->deleteTabungan($id) > 0) {
                    // oke
                    $this->response([
                        'status' => true,
                        'tabunganNo' => $id,
                        'message' => 'Data Tabungan berhasil di hapus.'
                    ],  REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'No Tabungan tidak di temukan.'
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
        }
    }

    public function createTabungan_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $tabunganNo = $this->byDate_get();
            $hariIni = date("Y-m-d H:i:s");

            $image = base64_decode($this->input->post("images"));
            $filename = $tabunganNo . '.' . 'jpg';

            $path = "uploads/bukti_transfer/" . $filename;
            //image uploading folder path
            file_put_contents($path, $image);
            $total = str_replace(".", ",", str_replace(",", "", $this->post('total')));

            $data = [
                'tabunganNo' => $tabunganNo,
                'tanggalTabungan' => $hariIni,
                'username' => $this->post('username'),
                'buktiTransfer' => $filename,
                'total' => $total,
                'type' => $this->tabungan::$TIPE_NABUNG,
                'status' => $this->tabungan::$STATUS_PENDING,
                'createBy' => $this->post('username'),
                'createDate' => $hariIni,
                'updateBy' => $this->post('username'),
                'updateDate' => $hariIni
            ];

            if ($this->tabungan->createDataTabungan($data) > 0) {
                $arrayDataAdmin = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Tabungan masuk dengan nomor $tabunganNo"
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

    public function createTariktunai_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $tabunganNo = $this->byDate_get();
            $hariIni = date("Y-m-d H:i:s");

            $total = str_replace(".", ",", str_replace(",", "", $this->post('total')));

            $data = [
                'tabunganNo' => $tabunganNo,
                'tanggalTabungan' => $hariIni,
                'username' => $this->post('username'),
                'buktiTransfer' => '',
                'total' => $total,
                'type' => $this->tabungan::$TIPE_TARIK_TUNAI,
                'status' => $this->tabungan::$STATUS_PENDING,
                'createBy' => $this->post('username'),
                'createDate' => $hariIni,
                'updateBy' => $this->post('username'),
                'updateDate' => $hariIni
            ];

            if ($this->tabungan->createDataTabungan($data) > 0) {
                $arrayDataAdmin = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Permintaaan Tarik tunai dengan nomor $tabunganNo"
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

    public function updateTabungan_put()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $update = $this->put('tabunganNo');
            $hariIni = date("Y-m-d H:i:s");
            $data = [
                'username' => $this->put('username'),
                'buktiTransfer' => $this->put('buktiTransfer'),
                'total' => $this->put('total'),
                'status' => $this->put('status'),
                'transferApproval' => $this->put('transferApproval'),
                'updateBy' => $this->put('updateBy'),
                'updateDate' => $hariIni
            ];

            if ($this->tabungan->updateTabungan($data, $update) > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'Update data Berhasil'
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Update Data Gagal'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function approve_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $update = $this->post('tabunganNo');
            $userID = $this->post('userID');
            $data = [
                'status' => $this->tabungan::$STATUS_APPROVE
            ];

            if ($this->tabungan->updateApprove($data, $update) > 0) {
                $arrayDataUser = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Tabungan dengan nomor " .$this->post('tabunganNo'). " Diterima"
                    ],
                    "data" => [
                        "tabunganNo" => $this->post('tabunganNo'),
                        "status" => $this->tabungan::$STATUS_APPROVE
                    ],
                    "to" => "/topics/$userID"
                );

                $message = "Tabungan dengan nomor " .$this->post('tabunganNo'). " Diterima";
                $debug =  $this->M_User->getUser($userID);
                $this->pushNotification($arrayDataUser, $userID, $message);
                $this->response([
                    'status' => true,
                    'message' => 'Data Berhasil Diapprove',
                    'DEBUG' => $debug[0]['username']
                ], REST_Controller::HTTP_CREATED);
            } else {
                $debug =  $this->user->getUser($userID);
                $this->response([
                    'status' => false,
                    'message' => 'Data Gagal Diapprove',
                    'DEBUG' => $debug[0]['username']
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function reject_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $update = $this->post('tabunganNo');
            $userID = $this->post('userID');
            $data = [
                'status' => $this->tabungan::$STATUS_REJECT
            ];

            if ($this->tabungan->updateReject($data, $update) > 0) {
                $arrayDataUser = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Tabungan dengan nomor " .$this->post('tabunganNo'). " Ditolak"
                    ],
                    "data" => [
                        "tabunganNo" => $this->post('tabunganNo'),
                        "status" => $this->tabungan::$STATUS_REJECT
                    ],
                    "to" => "/topics/$userID"
                );

                $message = "Tabungan dengan nomor " .$this->post('tabunganNo'). " Ditolak";
                $this->pushNotification($arrayDataUser, $userID, $message);
                $this->response([
                    'status' => true,
                    'message' => 'Data Berhasil Direject'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Gagal Direject'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function approveTariktunai_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $update = $this->post('tabunganNo');
            $userID = $this->post('userID');
            $data = [
                'status' => $this->tabungan::$STATUS_APPROVE
            ];

            if ($this->tabungan->updateApprove($data, $update) > 0) {
                $arrayDataUser = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Tarik tunai dengan nomor " .$this->post('tabunganNo'). " Diterima"
                    ],
                    "data" => [
                        "tabunganNo" => $this->post('tabunganNo'),
                        "status" => $this->tabungan::$STATUS_APPROVE
                    ],
                    "to" => "/topics/$userID"
                );

                $message = "Tarik tunai dengan nomor " .$this->post('tabunganNo'). " Diterima";
                $this->pushNotification($arrayDataUser, $userID, $message);
                $this->response([
                    'status' => true,
                    'message' => 'Data Berhasil Diapprove',
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Gagal Diapprove',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function approveTransaksi_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $update = $this->post('tabunganNo');
            $userID = $this->post('userID');
            $data = [
                'status' => $this->tabungan::$STATUS_APPROVE
            ];

            if ($this->transaksi->updateApprove($data, $update) > 0) {
                $arrayDataUser = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Transaksi dengan nomor " .$this->post('tabunganNo'). " Diterima"
                    ],
                    "data" => [
                        "tabunganNo" => $this->post('tabunganNo'),
                        "status" => $this->tabungan::$STATUS_APPROVE
                    ],
                    "to" => "/topics/$userID"
                );

                $message = "Transaksi dengan nomor " .$this->post('tabunganNo'). " Diterima";
                $this->pushNotification($arrayDataUser, $userID, $message);
                $this->response([
                    'status' => true,
                    'message' => 'Data Berhasil Diapprove',
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Gagal Diapprove',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function rejectTariktunai_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $update = $this->post('tabunganNo');
            $userID = $this->post('userID');
            $data = [
                'status' => $this->tabungan::$STATUS_REJECT
            ];

            if ($this->tabungan->updateReject($data, $update) > 0) {
                $arrayDataUser = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Tarik tunai dengan nomor " .$this->post('tabunganNo'). " Ditolak"
                    ],
                    "data" => [
                        "tabunganNo" => $this->post('tabunganNo'),
                        "status" => $this->tabungan::$STATUS_REJECT
                    ],
                    "to" => "/topics/$userID"
                );

                $message = "Tarik tunai dengan nomor " .$this->post('tabunganNo'). " Ditolak";
                $this->pushNotification($arrayDataUser, $userID, $message);
                $this->response([
                    'status' => true,
                    'message' => 'Data Berhasil Direject'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Gagal Direject'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function rejectTransaksi_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $update = $this->post('tabunganNo');
            $userID = $this->post('userID');
            $data = [
                'status' => $this->tabungan::$STATUS_REJECT
            ];

            if ($this->transaksi->updateReject($data, $update) > 0) {
                $arrayDataUser = array(
                    "notification" => [
                        "title" => "Pemberitahuan !",
                        "body" => "Transaksi dengan nomor " .$this->post('tabunganNo'). " Ditolak"
                    ],
                    "data" => [
                        "tabunganNo" => $this->post('tabunganNo'),
                        "status" => $this->tabungan::$STATUS_REJECT
                    ],
                    "to" => "/topics/$userID"
                );

                $message = "Transaksi dengan nomor " .$this->post('tabunganNo'). " Ditolak";
                $this->pushNotification($arrayDataUser, $userID, $message);
                $this->response([
                    'status' => true,
                    'message' => 'Data Berhasil Direject'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Gagal Direject'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function countTabungan_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $username = $this->post('username');
            $result = $this->tabungan->countTabungan($username);

            if ($result) {
                $this->response([
                    'status' => true,
                    'data' => $result,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function countTabunganAdmin_post()
    {
        if (!$this->data_global) {
            $this->failed("Anda tidak memiliki akses!");
        } else {
            $result = $this->tabungan->countTabunganAdmin();

            if ($result) {
                $this->response([
                    'status' => true,
                    'data' => $result,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data Not Found',
                ], REST_Controller::HTTP_NOT_FOUND);
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
                'smtp_pass' => '******', //password email 
                'mailtype' => 'html',
                'charset' => 'iso-8859-1',
                'wordwrap' => TRUE
            );

            // Content Massage
            $message = '<html><body>';
            $message .= '<img src="https://scontent.fcgk27-1.fna.fbcdn.net/v/t1.0-9/127182060_4719318924806241_2959586845410560513_n.jpg?_nc_cat=111&ccb=3&_nc_sid=730e14&_nc_eui2=AeEoaKUZYa2FdXLnW0kT9qxYi4hAJ5kU39iLiEAnmRTf2Mf3tJwWob-es9bebEQyOXSJKe8EuQUKfYN4dXOa9R3f&_nc_ohc=zlvdJ9Jo6r8AX-3Qh-x&_nc_oc=AQntGTmySDL1Nk2EoiHMIgg8pVMiha8wWzpJZqWoDd90cnNsltozt0aGBbEjP77nMj4&_nc_ht=scontent.fcgk27-1.fna&oh=bc27cd04ddc0048eb83aec73f445e4bd&oe=60549D63"  width="100" height="100" style="vertical-align:middle;margin:center" alt="Qurbanku"/>';
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
        $table = "tabungan";
        $field = "tabunganNo";

        //fungsi tanggal
        $today = date('ymd');

        //menampilkan TR201107
        $prefix = "TR" . $today;

        $lastKode = $this->tabungan->getMaxbyDate($prefix, $table, $field);

        // mengambil 4 karakter dari belakang
        $noUrut = (int) substr($lastKode, -4, 4);
        $noUrut++;

        $newKode = $prefix . sprintf('%04s', $noUrut);

        return $newKode;
        // var_dump($newKode);
    }

    public function test_post()
    {
        $userID = $this->post('userID');
        $arrayDataAdmin = array(
            "notification" => [
                "title" => "Pemberitahuan !",
                "body" => "Tabungan masuk dengan nomor "
            ],
            "data" => [
                "tabunganNo" => 'TR0000001',
                "status" => $this->tabungan::$STATUS_APPROVE
            ],
            "to" => "/topics/$userID"
        );

        $this->pushNotification($arrayDataAdmin);
        $this->response([
            'status' => true,
            'message' => 'Data Berhasil di Input',
        ], REST_Controller::HTTP_CREATED);
    }
}
