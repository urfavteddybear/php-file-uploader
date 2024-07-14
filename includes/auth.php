<?php
session_start();

// Replace these with your actual username and password
define('USERNAME', 'yourusername');
define('PASSWORD', 'yourpassword');

function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

function authenticate($username, $password) {
    if ($username === USERNAME && $password === PASSWORD) {
        $_SESSION['authenticated'] = true;
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
}
?>
