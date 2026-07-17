<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "innerbloom_db");
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $stmt = $conn->prepare("UPDATE user SET FIRST_NAME=?, LAST_NAME=?, PHONE_NUMBER=? WHERE USER_ID=2");
    $stmt->bind_param("ssi", $data['fname'], $data['lname'], $data['phone']);
    echo json_encode(["success" => $stmt->execute()]);
}
?>