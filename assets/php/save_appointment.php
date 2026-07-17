<?php
header('Content-Type: application/json');
ob_start(); // Buffer output to prevent stray characters
session_start(); // ✅ Start the session at the top

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
     header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "innerbloom_db");

$input = file_get_contents('php://input');
$data = json_decode($input, true);


if (!$data) {
    die(json_encode(["success" => false, "error" => "No data received"]));
}

// Clean name for lookup
$docName = str_replace(["Dr. ", "Dr "], "", $data['doctor_name']); 
$nameParts = explode(" ", $docName);
$first = $conn->real_escape_string($nameParts[0]);

// Find Therapist
$therapist = $conn->query("SELECT USER_ID FROM user WHERE FIRST_NAME LIKE '%$first%' AND ROLE = 'therapist' LIMIT 1")->fetch_assoc();

if (!$therapist) {
    ob_clean();
    echo json_encode(["success" => false, "error" => "Doctor '$first' not found in database."]);
    exit;
}

$tid = $therapist['USER_ID'];
$date = $conn->real_escape_string($data['date'] . " " . $data['time']);
$reason = $conn->real_escape_string($data['reason']);
$code = $conn->real_escape_string($data['code']);

$sql = "INSERT INTO session (PATIENT_ID, THERAPIST_ID, DATE, REASON, STATUS) 
        VALUES ($uid, $tid, '$date', '$reason', 'not_paid')";

ob_clean(); // Clear any warnings
if ($conn->query($sql)) {
    echo json_encode(["success" => true, "new_id" => $conn->insert_id]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}
?>