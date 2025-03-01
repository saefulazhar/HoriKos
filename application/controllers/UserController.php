<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Output $output
 * @property UserModel $UserModel
 */
class UserController extends CI_Controller {

    private $secret_key = "JanganGiveItTahu"; // Gunakan key yang lebih kuat

    public function __construct() {
        parent::__construct();
        $this->load->database();
        require_once APPPATH . '../vendor/autoload.php';
        $this->load->model('UserModel');
    }

    public function getProfile() {
        $headers = $this->input->request_headers();

        if (!isset($headers['Authorization'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Token tidak ditemukan']));
            return;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
            $user = $this->UserModel->get_user_by_id($decoded->id);

            if (!$user) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']));
                return;
            }

            // Mengembalikan data profil user
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                    ]
                ]));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Token tidak valid']));
        }
    }

    public function upgradeToPemilik() {
        header('Content-Type: application/json');
    
        // Ambil token dari header
        $headers = $this->input->request_headers();
        if (!isset($headers['Authorization'])) {
            echo json_encode(['status' => 'error', 'message' => 'Token tidak ditemukan']);
            return;
        }
    
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    
        // Decode token untuk mendapatkan user_id
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
            $user_id = $decoded->user_id; // Pastikan token menyimpan "user_id"
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Token tidak valid']);
            return;
        }
    
        // Load model
        $this->load->model('UserModel');
    
        // Ambil data user dari database
        $user = $this->UserModel->get_user_by_id($user_id);
        
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
            return;
        }
    
        // **Cek apakah nomor HP sudah diisi**
        if (empty($user->phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Silakan isi nomor HP sebelum menjadi pemilik kos']);
            return;
        }
    
        // Update role user menjadi "pemilik"
        if ($this->UserModel->updateUserRole($user_id, 'pemilik')) {
            echo json_encode(['status' => 'success', 'message' => 'Anda sekarang menjadi pemilik kos']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah role']);
        }
    }
    

    public function updateProfile()
{
    $headers = $this->input->get_request_header('Authorization');
    $token = str_replace('Bearer ', '', $headers);
    
    if (!$token) {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(401)
            ->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
    }
      
    try {
        $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
        $userId = $decoded->id;

        $input = json_decode($this->input->raw_input_stream, true);

        $updateData = [];
        if (isset($input['name'])) $updateData['name'] = $input['name'];
        if (isset($input['email'])) $updateData['email'] = $input['email'];
        if (isset($input['password'])) $updateData['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        if (isset($input['phone'])) $updateData['phone'] = $input['phone'];
        if (isset($input['address'])) $updateData['address'] = $input['address'];

        if (empty($updateData)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diperbarui']));
        }

        $this->load->model('UserModel');
        $update = $this->UserModel->updateUserProfile($userId, $updateData);

        if ($update) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui']));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil']));
        }
    } catch (Exception $e) {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(401)
            ->set_output(json_encode(['status' => 'error', 'message' => 'Token tidak valid']));
    }
}

}
