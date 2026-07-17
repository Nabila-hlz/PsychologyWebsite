<?php
//in logout.php + add the button in all dashboards to the logout.php link
//about logout: (Destroy session + Delete cookie + Delete token from DB)
//1 Start session to access the current session
//2 Remove remember-me token (if exists)
//3 Destroy the session
//4 Redirect user
require_once 'dbconnection.php';
session_start();

$_SESSION = [];
session_destroy();

if (isset($_COOKIE['remember_me'])) {
    
    $hashedToken = hash('sha256', $_COOKIE['remember_me']);
    $stmt = $conn->prepare("DELETE FROM  `remember_tokens` WHERE TOKEN_HASH = ?");
    $stmt->bind_param("s",$hashedToken);
    $stmt->execute();

    setcookie("remember_me", "", time() - 3600, "/"); 
}

header("Location: http://localhost/innerbloom/src/index.php"); //the auto one to test only -> must lead me to loginpage
exit;

$conn->close();
?>