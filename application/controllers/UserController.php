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
}
