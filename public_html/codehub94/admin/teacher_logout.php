<?php
session_start();

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear any additional cookies if set
if (isset($_COOKIE['user']) || isset($_COOKIE['pass'])) {
    setcookie('user', '', time() - 3600, '/');
    setcookie('pass', '', time() - 3600, '/');
}

// Redirect to the login page
header('Location: teacher_login.php');
exit();
?>
