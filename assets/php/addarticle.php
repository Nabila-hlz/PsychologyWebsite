<?php
session_start();
require_once 'dbconnection.php';
header('Content-Type: application/json');

// Check if user is logged in and is therapist or admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['therapist', 'admin'])) {
    echo json_encode([
        "success" => false,
        "message" => "You are not authorized to add articles."
    ]);
    exit;
}

// Check form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $specialty_id = intval($_POST['specialty_id'] ?? 0);
    $therapist_id = $_SESSION['user_id'];
    $views = 0;
    $type = 'Article';
    $created_at = date('Y-m-d H:i:s');
    $dbThumbPath = null;
    // File upload
    if (!isset($_FILES['articleFile']) || $_FILES['articleFile']['error'] !== UPLOAD_ERR_OK) {

        echo json_encode([
            "success" => false,
            "message" => "File upload failed!"
        ]);
        exit;
    }

    $fileTmpPath = $_FILES['articleFile']['tmp_name'];
    $fileName = basename($_FILES['articleFile']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExt !== 'pdf') {

        echo json_encode([
            "success" => false,
            "message" => "Only PDF files are allowed!"
        ]);
        exit;
    }

    // Prepare upload paths
    $uploadsDir = __DIR__ . "/../uploads/articles/"; // absolute server path
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true); // create folder if it doesn't exist
    }
    // Sanitize original file name (remove spaces/special chars)
    $originalName = pathinfo($_FILES['articleFile']['name'], PATHINFO_FILENAME);
    $originalName = preg_replace("/[^a-zA-Z0-9_-]/", "_", $originalName);

    // Keep original extension
    $fileExt = strtolower(pathinfo($_FILES['articleFile']['name'], PATHINFO_EXTENSION));

    // Create unique file name
    $newFileName = $originalName . '_' . uniqid() . '.' . $fileExt;

    // Full path to move the file
    $fileDestination = $uploadsDir . $newFileName;

    // Path to store in DB (relative to your web root)
    $dbFilePath = "assets/uploads/articles/" . $newFileName;
    

    // Prepare upload paths for thumbnail
    $thumbsDir = __DIR__ . "/../uploads/articlesThumbnails/";
    if (!is_dir($thumbsDir)) {
        mkdir($thumbsDir, 0777, true);
    }

    $thumbFile = $_FILES['articleThumbnail'];
    $thumbExt = pathinfo($thumbFile['name'], PATHINFO_EXTENSION);
    $thumbName = uniqid('thumb_', true) . "." . $thumbExt;
    $thumbDestination = $thumbsDir . $thumbName;
    $dbThumbPath = "assets/uploads/articlesThumbnails/" . $thumbName;



    try {

        // Start transaction
        $conn->begin_transaction();
        // Move uploaded file to the destination
        if (!move_uploaded_file($_FILES['articleFile']['tmp_name'], $fileDestination)) {
            throw new Exception("Failed to move uploaded file to uploads/articles folder.");
        }

        if (!move_uploaded_file($thumbFile['tmp_name'], $thumbDestination)) {
            throw new Exception("Failed to move uploaded thumbnail.");
        }


        // Insert article into DB
        $stmt = $conn->prepare("INSERT INTO CONTENT 
            (TYPE, PATH, TITLE, DISCRIPTION, CREATED_AT, THERAPIST_ID, SPECIALTY_ID, VIEWS, THUMBNAIL_PATH) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssiiss",
            $type,
            $dbFilePath,
            $title,
            $description,
            $created_at,
            $therapist_id,
            $specialty_id,
            $views,
            $dbThumbPath
        );

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        $conn->commit();
        // Send JSON success
        echo json_encode([
            "success" => true,
            "message" => "Article uploaded successfully!"
        ]);
        exit;
    } catch (Exception $e) {
        $conn->rollback();

        // Delete uploaded files if they exist
        if (file_exists($pdfFilePath)) unlink($pdfFilePath);
        if (file_exists($thumbFilePath)) unlink($thumbFilePath);

        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
        exit;
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
    exit;
}
