<?php
session_start();
require_once 'dbconnection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_videos': getVideos($conn); break;
    case 'get_specialties': getSpecialties($conn); break;
    case 'add_video': 
        requireRole(['therapist', 'admin']); 
        addVideo($conn); 
        break;
    case 'delete_video': 
        requireRole(['admin']); 
        deleteVideo($conn); 
        break;
    case 'increment_views': incrementViews($conn); break;
    default: echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function requireRole(array $roles) {
    if (!in_array($_SESSION['role'], $roles, true)) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

function addVideo($conn) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $specialty_id = $_POST['specialty_id'] ?? null;
    $video_url = trim($_POST['video_url'] ?? '');
    $therapist_id = $_SESSION['user_id'];

    if (empty($title) || empty($video_url) || empty($specialty_id)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    // Extract YouTube ID and create Embed URL
    $youtube_id = "";
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match)) {
        $youtube_id = $match[1];
    }

    if (empty($youtube_id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid YouTube URL']);
        return;
    }

    $embed_path = "https://www.youtube.com" . $youtube_id;
    $thumbnail_path = "https://img.youtube.com" . $youtube_id . "/hqdefault.jpg";

    $stmt = $conn->prepare("INSERT INTO content (TYPE, PATH, THUMBNAIL_PATH, TITLE, DISCRIPTION, THERAPIST_ID, SPECIALTY_ID, VIEWS, CREATED_AT) VALUES ('Video', ?, ?, ?, ?, ?, ?, 0, NOW())");
    $stmt->bind_param("ssssii", $embed_path, $thumbnail_path, $title, $description, $therapist_id, $specialty_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'YouTube video added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    $stmt->close();
}

function getVideos($conn) {
    $sql = "SELECT c.*, s.SPECIALTY_NAME, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS doctor_name 
            FROM content c 
            JOIN therapist t ON c.THERAPIST_ID = t.THERAPIST_ID 
            JOIN user u ON u.USER_ID = t.THERAPIST_ID 
            LEFT JOIN specialty s ON s.SPECIALTY_ID = c.SPECIALTY_ID 
            WHERE c.TYPE = 'Video' ORDER BY c.CREATED_AT DESC";
    $result = $conn->query($sql);
    $videos = [];
    while ($row = $result ? $result->fetch_assoc() : []) { $videos[] = $row; }
    echo json_encode(['success' => true, 'videos' => $videos]);
}

function getSpecialties($conn) {
    $result = $conn->query("SELECT SPECIALTY_ID, SPECIALTY_NAME FROM specialty ORDER BY SPECIALTY_NAME");
    $data = [];
    while ($row = $result->fetch_assoc()) { $data[] = $row; }
    echo json_encode(['success' => true, 'specialties' => $data]);
}

function deleteVideo($conn) {
    $video_id = (int)($_POST['video_id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM content WHERE CONTENT_ID = ? AND TYPE = 'Video'");
    $stmt->bind_param("i", $video_id);
    echo json_encode(['success' => $stmt->execute()]);
    $stmt->close();
}

function incrementViews($conn) {
    $video_id = (int)($_POST['video_id'] ?? 0);
    $stmt = $conn->prepare("UPDATE content SET VIEWS = VIEWS + 1 WHERE CONTENT_ID = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
}
$conn->close();
