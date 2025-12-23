<?php
// includes/auth.php

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Simple demo login (replace with proper authentication)
function demoLogin($username, $password) {
    // This is just for demo - in production use proper password hashing
    $valid_users = [
        'demo' => password_hash('demo123', PASSWORD_DEFAULT),
        'admin' => password_hash('admin123', PASSWORD_DEFAULT)
    ];
    
    if (isset($valid_users[$username]) && password_verify($password, $valid_users[$username])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = ($username == 'admin') ? 'admin' : 'user';
        return true;
    }
    
    return false;
}