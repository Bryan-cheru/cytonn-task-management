<?php
session_start();

class Auth {
    
    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
    }
    
    public static function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit();
    }
    
    public static function check() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function requireAuth() {
        if (!self::check()) {
            header('Location: ../index.php');
            exit();
        }
    }
    
    public static function requireAdmin() {
        self::requireAuth();
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: /dashboard.php');
            exit();
        }
    }
    
    public static function user() {
        if (self::check()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }
    
    public static function id() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function isAdmin() {
        return $_SESSION['user_role'] === 'admin';
    }
    
    public static function isUser() {
        return $_SESSION['user_role'] === 'user';
    }
}
?>
