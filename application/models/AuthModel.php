<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthModel extends CI_Model {

    private $secret_key = "JanganGiveItTahu";
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Cek apakah email sudah terdaftar
    public function check_email_exists($email) {
        return $this->db->where('email', $email)->get('users')->row();
    }

    public function register_user($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => 'pencari', // Default role
            'status' => 'aktif'
        ];
        return $this->db->insert('users', $userData);
    }

    
    // Simpan user baru ke database
    public function get_user_by_email($email) {
        return $this->db->where('email', $email)->get('users')->row();
    }
    
    public function save_token($user_id, $token) {
        $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
        // Decode token
        $expires_at = date("Y-m-d H:i:s", $decoded->exp); // Konversi ke format DATETIME
        
        $data = [
            'user_id' => $user_id,
            'token' => $token,
            'created_at' => date("Y-m-d H:i:s"),
            'expires_at' => $expires_at
        ];
        
        return $this->db->insert('user_login_tokens', $data);
    }
    
    
    
    // Hapus token saat logout
    public function delete_token($token) {
        if (!$token) {
            return false;
        }
        
        $this->db->where('token', $token);
        $this->db->delete('user_login_tokens');
        
        return $this->db->affected_rows() > 0; // Mengembalikan true jika ada data yang terhapus
    }
    
    public function delete_expired_tokens() {
        $this->db->where('expires_at <', date("Y-m-d H:i:s"));
        $this->db->delete('user_login_tokens');
    }
    
    
}
