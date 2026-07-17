<?php
/*require_once 'dbconnection.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT specialty_id, name FROM `specialty` ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $specialties = [];
    while ($row = $result->fetch_assoc()) {
        $specialties[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'specialties' => $specialties
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}*/
?>