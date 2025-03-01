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

    // Registrasi user baru
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

    // Ambil user berdasarkan email
    public function get_user_by_email($email) {
        return $this->db->where('email', $email)->get('users')->row();
    }

    // Generate JWT token
    public function generate_token($user) {
        $payload = [
            'id'    => $user->id,
            'email' => $user->email,
            'role'  => $user->role,
            'iat'   => time(), // Issued at
        ];

        return JWT::encode($payload, $this->secret_key, 'HS256');
    }

    // Simpan token login ke database
    public function save_token($user_id, $token) {
        $data = [
            'user_id'    => $user_id,
            'token'      => $token,
            'created_at' => date("Y-m-d H:i:s"),
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
}
