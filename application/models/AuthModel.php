<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthModel extends CI_Model {

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
        return $this->db->insert('user_login_tokens', ['user_id' => $user_id, 'token' => $token]);
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
