<?php
require_once 'dbconnection.php';

header('Content-Type: application/json');

/* ===============================
   INPUT
   therapist_id is OPTIONAL
================================ */
$therapistId = isset($_GET['therapist_id'])
    ? (int)$_GET['therapist_id']
    : null;

/* ===============================
   SQL
================================ */
if ($therapistId) {

    // Reviews for ONE therapist (CORRECT LOGIC)
    $sql = "
    SELECT
        r.COMMENT,
        r.CREATED_AT,
        CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS USER_NAME
    FROM review r
    JOIN user u
        ON u.USER_ID = r.USER_ID
    JOIN session s
        ON s.PATIENT_ID = r.USER_ID
    WHERE s.THERAPIST_ID = ?
    ORDER BY r.CREATED_AT DESC
    LIMIT 4
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $therapistId);

} else {

    // Global latest reviews (homepage slider)
    $sql = "
    SELECT
        r.COMMENT,
        r.CREATED_AT,
        CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS USER_NAME
    FROM review r
    JOIN user u
        ON u.USER_ID = r.USER_ID
    ORDER BY r.CREATED_AT DESC
    LIMIT 4
    ";

    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

/* ===============================
   FORMAT RESPONSE
================================ */
$reviews = [];

while ($row = $result->fetch_assoc()) {
    $reviews[] = [
        'comment' => $row['COMMENT'],
        'user_name' => $row['USER_NAME'],
        'created_at' => $row['CREATED_AT']
    ];
}

/* ===============================
   OUTPUT
================================ */
echo json_encode([
    'success' => true,
    'count' => count($reviews),
    'reviews' => $reviews
]);

$conn->close();
?>