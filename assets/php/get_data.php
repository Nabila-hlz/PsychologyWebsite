<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "innerbloom_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

// For this demo, we assume the logged-in user is ID 1
$userId = 1;

// 1. Get User Profile
$userQuery = $conn->query("SELECT FIRST_NAME, LAST_NAME, EMAIL, PHONE_NUMBER FROM user WHERE USER_ID = $userId");
$userData = $userQuery->fetch_assoc();

// 2. Get Sessions (Joining with User table to get Therapist Name)
$sessionQuery = $conn->query("
    SELECT s.SESSION_ID as id, s.DATE as datetime, s.REASON as reason, s.STATUS as status,
           u.FIRST_NAME as th_fname, u.LAST_NAME as th_lname
    FROM session s
    JOIN user u ON s.THERAPIST_ID = u.USER_ID
    WHERE s.PATIENT_ID = $userId
    ORDER BY s.DATE DESC
");

$sessions = [];
while($row = $sessionQuery->fetch_assoc()) {
    $row['doctor'] = "Dr. " . $row['th_fname'] . " " . $row['th_lname'];
    $sessions[] = $row;
}

echo json_encode([
    "user" => $userData,
    "sessions" => $sessions
]);
?>