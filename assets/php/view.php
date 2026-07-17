<?php
require_once 'dbconnection.php';

$id = intval($_GET['id']); // article ID

// Get the file path first
$result = $conn->query("SELECT PATH FROM CONTENT WHERE CONTENT_ID = $id AND TYPE='Article'");
if (!$result || $result->num_rows === 0) {
    die("Article not found.");
}

$row = $result->fetch_assoc();
$direction = '../../' . $row['PATH']; // stored in DB like "assets/uploads/articles/filename.pdf"

// increment views
$conn->query("UPDATE CONTENT SET VIEWS = VIEWS + 1 WHERE CONTENT_ID = $id");

// redirect to the PDF
header("Location: $direction");
exit;







?>
