<?php
header('Content-Type: application/json');
session_start(); // ✅ Start the session at the top

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
     header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "innerbloom_db");

if ($conn->connect_error) die(json_encode(["error" => "Connection failed"]));



// 1. Get Profile
$user = $conn->query("SELECT * FROM user WHERE USER_ID = $uid")->fetch_assoc();

// 2. Get Sessions + Therapist Names
$sessions = [];
$res = $conn->query("
    SELECT 
        s.*,
        u.FIRST_NAME AS th_f,
        u.LAST_NAME AS th_l,
        t.SESSION_PRICE,
        t.BIO
    FROM session s
    JOIN user u ON s.THERAPIST_ID = u.USER_ID
    JOIN therapist t ON s.THERAPIST_ID = t.THERAPIST_ID
    WHERE s.PATIENT_ID = $uid
    ORDER BY s.DATE ASC
");


while($row = $res->fetch_assoc()) {
    $row['doctor'] = "Dr. " . $row['th_f'] . " " . $row['th_l'];
    $sessions[] = $row;
}

echo json_encode(["user" => $user, "sessions" => $sessions]);
?>