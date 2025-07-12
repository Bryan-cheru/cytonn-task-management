<?php
require_once __DIR__ . '/../../app/Models/User.php';
require_once __DIR__ . '/../../app/Auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        header('Location: ../index.php?error=1');
        exit();
    }
    
    $userModel = new User();
    $user = $userModel->verifyCredentials($email, $password);
    
    if ($user) {
        Auth::login($user);
        header('Location: ../dashboard.php');
        exit();
    } else {
        header('Location: ../index.php?error=1');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>
