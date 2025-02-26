<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verify_jwt($token) {
    $CI = &get_instance();
    $secret_key = "RAHASIA_JWT_KAMU"; // Sama dengan yang di AuthController

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        return $decoded; // Jika berhasil, kembalikan data user
    } catch (Exception $e) {
        return false; // Token tidak valid atau expired
    }
}
