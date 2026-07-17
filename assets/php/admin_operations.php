<?php
session_start();
require_once 'dbconnection.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

/*
 DEV MODE:
 - If not logged in → return EMPTY but valid JSON
*/
if (!isset($_SESSION['user_id'])) {
  echo json_encode([
    'success' => true,
    'users' => [],
    'therapists' => [],
    'appointments' => [],
    'videoCount' => 0,
    'articleCount' => 0
  ]);
  exit;
}

switch ($action) {
case 'get_dashboard_data':
  
  // USERS
  $users = [];
  $q = "
    SELECT USER_ID AS id,
           FIRST_NAME AS first_name,
           LAST_NAME AS last_name,
           EMAIL AS email,
           PHONE_NUMBER AS phone,
           ROLE AS role,
           GENDER AS gender,
           CREATED_AT AS created_at
    FROM user
    WHERE ROLE = 'patient'
    ORDER BY CREATED_AT DESC
  ";
  $r = $conn->query($q);
  while ($row = $r->fetch_assoc()) $users[] = $row;

  // THERAPISTS
  $therapists = [];
  $q = "
    SELECT
      u.USER_ID AS id,
      CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS name,
      u.EMAIL AS email,
      u.PHONE_NUMBER AS phone,
      u.CREATED_AT AS created_at,
      t.SESSION_PRICE AS session_price,
      GROUP_CONCAT(DISTINCT s.SPECIALTY_NAME SEPARATOR ', ') AS specialties
    FROM user u
    LEFT JOIN therapist t ON u.USER_ID = t.THERAPIST_ID
    LEFT JOIN specialty_therapist st ON t.THERAPIST_ID = st.THERAPIST_ID
    LEFT JOIN specialty s ON st.SPECIALTY_ID = s.SPECIALTY_ID
    WHERE u.ROLE = 'therapist'
    GROUP BY u.USER_ID
    ORDER BY u.CREATED_AT DESC
  ";
  $r = $conn->query($q);
  while ($row = $r->fetch_assoc()) $therapists[] = $row;

  // APPOINTMENTS
  $appointments = [];
  $q = "
    SELECT
      s.SESSION_ID AS id,
      s.DATE AS date,
      s.STATUS AS status,
      s.REASON AS reason,
      s.CREATED_AT AS created_at,
      CONCAT(p.FIRST_NAME, ' ', p.LAST_NAME) AS patient_name,
      CONCAT(t.FIRST_NAME, ' ', t.LAST_NAME) AS therapist_name
    FROM session s
    JOIN user p ON s.PATIENT_ID = p.USER_ID
    JOIN user t ON s.THERAPIST_ID = t.USER_ID
    ORDER BY s.DATE DESC
  ";
  $r = $conn->query($q);
  while ($row = $r->fetch_assoc()) $appointments[] = $row;

  // COUNT VIDEOS AND ARTICLES
  $videoCount = (int)$conn->query("SELECT COUNT(*) AS total FROM content WHERE TYPE = 'Video'")->fetch_assoc()['total']; 
  $articleCount = (int)$conn->query("SELECT COUNT(*) AS total FROM content WHERE TYPE = 'Article'")->fetch_assoc()['total'];

  echo json_encode([
    'success' => true,
    'users' => $users,
    'therapists' => $therapists,
    'appointments' => $appointments,
    'videoCount' => $videoCount,
    'articleCount' => $articleCount
  ]);
  break;

default:
  echo json_encode(['success'=>false,'message'=>'Invalid action']);
}

$conn->close();
?>
