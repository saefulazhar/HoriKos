<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_user_by_id($id) {
        return $this->db->get_where('users', ['id' => $id])->row();
    }

    public function updateUserRole($user_id, $new_role) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', ['role' => $new_role]);
    }
    
}
