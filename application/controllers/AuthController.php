<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 * @property CI_Output $output
 * @property AuthModel $AuthModel
 */
class AuthController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        require_once APPPATH . '../vendor/autoload.php';
        $this->load->model('AuthModel');
        $this->load->helper(array('url', 'form'));
        $this->load->library('form_validation');
    }

    public function index() {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Silakan gunakan endpoint API yang sesuai']);
    }

    public function register() {
        header('Content-Type: application/json');
    
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (!$data || empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
            return;
        }
    
        if ($this->AuthModel->check_email_exists($data['email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Email sudah digunakan']);
            return;
        }
    
        if ($this->AuthModel->register_user($data)) {
            echo json_encode(['status' => 'success', 'message' => 'Registrasi berhasil']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mendaftar, coba lagi']);
        }
    }

    public function login() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
            return;
        }
    
        $user = $this->AuthModel->get_user_by_email($data['email']);
        
        if (!$user || !password_verify($data['password'], $user->password)) {
            echo json_encode(['status' => 'error', 'message' => 'Email atau password salah']);
            return;
        }
    
        $token = $this->AuthModel->generate_token($user); // Panggil dari Model
        $this->AuthModel->save_token($user->id, $token);
    
        echo json_encode(['status' => 'success', 'message' => 'Login berhasil', 'token' => $token]);
    }

    public function logout() {
        $headers = $this->input->request_headers();
        if (!isset($headers['Authorization'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Token tidak ditemukan']));
            return;
        }
    
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    
        if ($this->AuthModel->delete_token($token)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Logout berhasil dan token dihapus']));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Token gagal dihapus']));
        }
    }

    public function registerPage() {
        $this->load->view('auth/register'); // Load view untuk form register
    }

    public function loginPage() {
        $this->load->view('auth/login'); // Load view untuk form login
    }

    
    
}
