<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../../PHPMailer/Exception.php';
require_once '../../PHPMailer/PHPMailer.php';
require_once '../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "dbconnection.php";

if (!isset($_SESSION['user_id'])) {
    //  redirect to login page
    header("Location: ../../assets/index.php");
    exit;
}
$therapist_id=$_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = $_POST['session_id'] ?? 0;
    $therapist_id = $_SESSION['user_id'] ?? 0;
    
    // verifying if session belongs to therapist
      $query = "SELECT s.*, u.EMAIL as patient_email, u.FIRST_NAME, u.LAST_NAME , s.meeting_url, s.meeting_room, s.meeting_password
              FROM session s 
              JOIN user u ON s.PATIENT_ID = u.USER_ID 
              WHERE s.Session_ID = ? AND s.THERAPIST_ID = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $session_id, $therapist_id);
    mysqli_stmt_execute($stmt);
    $session = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if (!$session) {
        // showing error and go back
        echo "<script>
            alert('Session not found');
            window.history.back();
        </script>";
        exit;
    }
    error_log("Session date value: " . ($session['DATE'] ?? 'NOT SET'));
error_log("Session array keys: " . implode(', ', array_keys($session)));

// checking date
if (!isset($session['DATE']) || empty($session['DATE'])) {
    error_log("ERROR: Date field is empty or doesn't exist!");
    echo "<script>
        alert('Error: Session date not found in database');
        window.history.back();
    </script>";
    exit;
}

$sessionTime = strtotime($session['DATE']);
error_log("Parsed session timestamp: $sessionTime = " . date('Y-m-d H:i:s', $sessionTime));
    // checking if within time interval (-7 to +7 minutes)
    $currentTime = time();
    $timeDifference = abs($currentTime - $sessionTime) / 60; // to be in minutes

    if ($timeDifference > 7) {
        echo "<script>
            alert('Meeting can only be started 7 minutes before/after scheduled time');
            window.history.back();
        </script>";
        exit;
    }
    
    // creating or get existing meeting
    if (empty($session['meeting_url'])) {
         $roomName = 'innerbloom-' . $therapist_id . '-' . $session_id . '-' . time();
        $password = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
        
        // patient name variable for email
        $patient_name = $session['FIRST_NAME'] . ' ' . $session['LAST_NAME'];
        $patient_email = $session['patient_email'];
        
        $meetingUrl = $meetingUrl = "https://meet.jit.si/" . $roomName . 
             "?jitsi_meet_external_api_id=moderator" .
             "&config.prejoinPageEnabled=false" .
             "&config.enableWelcomePage=false" .
             "&config.requireDisplayName=false" .
             "&interfaceConfig.SHOW_WATERMARK_FOR_GUESTS=false" .
             "&userInfo.displayName=Therapist" .
             "&userInfo.email=therapist@innerbloom.com" .
             "&appData.localStorageContent=moderator:true";
        
        // updating db
        $updateQuery = "UPDATE session SET 
                        meeting_url = ?,
                        meeting_room = ?,
                        meeting_password = ?,
                        status = 'completed',
                        meeting_started_at = NOW()
                        WHERE Session_ID = ?";
        
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "sssi", 
            $meetingUrl, $roomName, $password, $session_id);
        mysqli_stmt_execute($updateStmt);
        
    } else {
        $meetingUrl = $session['meeting_url'];
        $roomName = $session['meeting_room'];
        $password = $session['meeting_password'];
        $patient_name = $session['FIRST_NAME'] . ' ' . $session['LAST_NAME'];
        $patient_email = $session['patient_email'];
    }
    
    // Sending email to scheduled session patient
    $to = $patient_email;
    $subject = "Your Therapy Session is Ready";
    
    $message = "Dear " . $session['FIRST_NAME'] . ",\n\n" .
               "Your therapist has started your video session.\n\n" .
               "Join your session now:\n" .
               "🔗 https://meet.jit.si/" . $roomName . "\n\n" .
               ($password ? "Room Password: " . $password . "\n\n" : "") .
               "Click the link above to join the video call.\n\n" .
               "Best regards,\nInnerBloom Team";
    
    // PHPMailer calling
    $emailSent = sendEmail($to, $subject, $message);
    
    // redirecting to dashboard with success message
    if ($emailSent) {
        // Store meeting URL in session for dashboard to show
        $_SESSION['meeting_created'] = [
            'url' => $meetingUrl,
            'patient_name' => $session['FIRST_NAME'] . ' ' . $session['LAST_NAME']
        ];
        
        header("Location: " . $meetingUrl);
        exit;
    } else {
        // Still redirect but show alert
        echo "<script>
            alert('Meeting created but email failed to send. Join: $meetingUrl');
            window.location.href = 'psychologist_dashboard.php';
        </script>";
        exit;
    }
}

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'innerbloomdz@gmail.com';
        $mail->Password = 'yttzrxksrgniskzo';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('innerbloomdz@gmail.com', 'InnerBloom');
        $mail->addAddress($to);
        $mail->isHTML(true);
        
        // HTML version
        $mail->Subject = $subject;
        $mail->Body = nl2br($message);
        $mail->AltBody = $message; // plaintext version
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
    
}

// in case accessed directly (not POST), redirect
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: psychologist_overview.php");
    exit;
}

?>