<?php
require_once "dbconnection.php";
session_start();
if (!isset($_SESSION['user_id'])) {
    //  redirect to login page
    header("Location: ../../assets/index.php");
    exit;
}
$therapist_id=$_SESSION['user_id'];
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$therapist_id = $_SESSION['user_id'];

// getting json input
$input = json_decode(file_get_contents('php://input'), true);
$bio = $input['bio'] ?? '';

if (empty($bio)) {
    echo json_encode(['success' => false, 'error' => 'Bio is required']);
    exit;
}



// only the BIO field can be updated 
$query = "UPDATE therapist SET BIO = ? WHERE THERAPIST_ID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "si", $bio, $therapist_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Bio updated successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>