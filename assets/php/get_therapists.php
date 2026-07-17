<?php
require_once 'dbconnection.php';

header('Content-Type: application/json');

// Fetch therapists with their info
$sql = "SELECT 
        u.USER_ID,
        CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as name,
        t.PHOTO_PATH
        FROM user u
        INNER JOIN therapist t ON u.USER_ID = t.THERAPIST_ID
        WHERE u.ROLE = 'therapist'
        ORDER BY u.CREATED_AT DESC
        LIMIT 12";

$result = $conn->query($sql);
$therapists = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $therapists[] = $row;
    }
    echo json_encode(['success' => true, 'therapists' => $therapists]);
} else {
    echo json_encode(['success' => false, 'therapists' => [], 'message' => 'No therapists found']);
}

$conn->close();
?>