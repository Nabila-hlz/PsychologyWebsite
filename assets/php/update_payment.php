<?php
header('Content-Type: application/json');



$conn = new mysqli("localhost", "root", "", "innerbloom_db");
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $session_id = (int)$data['id']; // safety
    $amount = 1500;                 // or get from frontend
    $status = 'paid';

    // 1️⃣ Insert payment
    $conn->query("
        INSERT INTO payment (AMOUNT, STATUS, SESSION_ID)
        VALUES ($amount, '$status', $session_id)
    ");

    // 2️⃣ Update session status
    $stmt = $conn->prepare("
        UPDATE session 
        SET STATUS = 'scheduled' 
        WHERE SESSION_ID = ?
    ");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();

     $q = $conn->query("
        SELECT u.EMAIL, u.FIRST_NAME
        FROM session s
        JOIN user u ON s.PATIENT_ID = u.USER_ID
        WHERE s.SESSION_ID = $session_id
        LIMIT 1 
        ");
$user = $q->fetch_assoc();
















    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Session not found or already scheduled"
        ]);
    }

    $stmt->close();
}
?>
