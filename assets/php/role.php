<?php
require_once 'dbconnection.php';
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    echo "const userRole = '" . addslashes($_SESSION['role']) . "';";
    echo "const userId = '" . addslashes($_SESSION['user_id']) . "';";
    echo "const loggedIn = true;";
} else {
    echo "const loggedIn = false;";
    echo "const userRole = '';";
    echo "const userId = '';";
}
?>