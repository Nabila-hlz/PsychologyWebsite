<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "innerbloom_db");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

$res = $conn->query("
    SELECT FIRST_NAME
    FROM `user`
    WHERE ROLE = 'therapist'
    ORDER BY FIRST_NAME
");

$doctors = [];

while ($row = $res->fetch_assoc()) {
   $doctors[] = "Dr. " . $row['FIRST_NAME'];
}

echo json_encode($doctors);
