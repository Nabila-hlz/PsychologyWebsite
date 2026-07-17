<?php
// auto login by the token and saved cookie before login.html

session_start();
require_once "dbconnection.php";

// If user is already logged in → redirect

if (isset($_SESSION['user_id'])) {
    $userid = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    switch ($role) {
        case 'admin':
            header("Location:../php/admin_dashboard.php");
            break;
        case 'therapist':
            header("Location: ../../src/psychologist.html");
            break;
        case 'patient':
            header("Location: ../../src/user_dashboard.html");
            break;
        default:
            header("Location: ../../src/loginPage.php");
    }
    exit;
}

// If remember-me cookie exists → try auto login
elseif (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $hashedToken = hash('sha256', $token); //produce same as stored cuz it's deterministic

    $stmt = $conn->prepare("SELECT u.USER_ID, u.ROLE
                            FROM REMEMBER_TOKENS rt
                            JOIN USER u ON u.USER_ID = rt.USER_ID
                            WHERE rt.TOKEN_HASH = ?
                            AND rt.EXPIRE_DATE > NOW()
                            LIMIT 1");
    $stmt->bind_param('s', $hashedToken);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Valid token → auto login & create new session:
        $_SESSION['user_id'] = $user['USER_ID'];
        $_SESSION['role'] = $user['ROLE'];

        switch ($user['role']) {
            case 'admin':
                header("Location: ../php/admin_dashboard.php");
                break;
            case 'therapist':
                header("Location: ../../src/psycologist.html");
                break;
            case 'patient':
                header("Location: ../src/user_dashboard.html");
                break;
            default:
                header("Location: ../../src/loginPage.php");
        }
        exit;
    }
} else {
    // Invalid token or expired token → delete cookie
    setcookie("remember_me", "", time() - 3600, "/"); //it'll be deleted in the login if he check remember_me box again
    header("Location: ../../src/loginPage.php");
    exit;
}
$conn->close();
?>