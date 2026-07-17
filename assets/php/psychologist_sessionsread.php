<?php
require_once '../../PHPMailer/Exception.php';
require_once '../../PHPMailer/PHPMailer.php';
require_once '../../PHPMailer/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
require_once "dbconnection.php";

if (!isset($_SESSION['user_id'])) {
    //  redirect to login page
    header("Location: ../../assets/index.php");
    exit;
}
$therapist_id=$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = $_POST['session_id'] ?? 0;
    
    if (!$session_id) {
        echo "Error: No session ID provided !";
        exit;
    }

    $verifyQuery = "SELECT Session_ID FROM session WHERE Session_ID = ? AND THERAPIST_ID = ?";
    $verifyStmt = mysqli_prepare($conn, $verifyQuery);
    mysqli_stmt_bind_param($verifyStmt, "ii", $session_id, $therapist_id);
    mysqli_stmt_execute($verifyStmt);
    mysqli_stmt_store_result($verifyStmt);
    
    if (mysqli_stmt_num_rows($verifyStmt) === 0) {
        echo "Error: Session not found or unauthorized";
        mysqli_stmt_close($verifyStmt);
        mysqli_close($conn);
        exit;
    }
    
    $updateQuery = "UPDATE session SET Status = 'Cancelled' WHERE Session_ID = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "i", $session_id);
    
    if (mysqli_stmt_execute($updateStmt)) {
        $patientQuery = "
            SELECT u.EMAIL, u.FIRST_NAME, u.LAST_NAME 
            FROM session s 
            JOIN user u ON s.PATIENT_ID = u.USER_ID 
            WHERE s.Session_ID = ?
        ";
        $patientStmt = mysqli_prepare($conn, $patientQuery);
        mysqli_stmt_bind_param($patientStmt, "i", $session_id);
        mysqli_stmt_execute($patientStmt);
        $patientResult = mysqli_stmt_get_result($patientStmt);
        $patient = mysqli_fetch_assoc($patientResult);
        
        if ($patient && $patient['EMAIL']) {
            try {
                $mail = new PHPMailer(true);
                
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';  // Gmail SMTP
                $mail->SMTPAuth   = true;
                $mail->Username   = 'innerbloomdz@gmail.com';  // Your Gmail
                $mail->Password   = 'yttzrxksrgniskzo';  // Use App Password, NOT your regular password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // recipient
                $mail->setFrom('innerbloomdz@gmail.com', 'InnerBloom');
                $mail->addAddress($patient['EMAIL'], $patient['FIRST_NAME'] . ' ' . $patient['LAST_NAME']);
                
                // Content
                $mail->isHTML(false);  // Set to true if you want html emails
                $mail->Subject = 'Session Cancellation Notice';
                
                $message = "Dear " . $patient['FIRST_NAME'] . ",\n\n";
                $message .= "Your upcoming therapy session has been cancelled.\n";
                $message .= "Please feel free to reschedule.\n\n";
                $message .= "Best regards,\nInnerBloom";
                
                $mail->Body = $message;
                
                $mail->send();
                $emailStatus = " (Email sent successfully)";
            } catch (Exception $e) {
                $emailStatus = " (Email failed: " . $mail->ErrorInfo . ")";
                // Log the error or handle it as needed
                error_log("Email error: " . $mail->ErrorInfo);
            }
        } else {
            $emailStatus = " (No patient email found)";
        }
        
        echo "Session cancelled successfully. Patient has been notified.";
    } else {
        echo "Failed to cancel session. Please try again.";
    }
    
    mysqli_stmt_close($verifyStmt);
    mysqli_stmt_close($updateStmt);
    if (isset($patientStmt)) mysqli_stmt_close($patientStmt);
    mysqli_close($conn);
    exit;
}

$query_sessions = "
SELECT 
    s.Session_ID,
    u.FIRST_NAME , 
    u.LAST_NAME, 
    s.DATE , 
    s.Status,
    s.reason
FROM 
    session s
JOIN 
    user u ON s.PATIENT_ID = u.USER_ID
WHERE 
    s.THERAPIST_ID = ?
ORDER BY 
    s.DATE DESC;
";
$stmt_sessions = mysqli_prepare($conn, $query_sessions);
mysqli_stmt_bind_param($stmt_sessions, "i", $therapist_id);
mysqli_stmt_execute($stmt_sessions);
$result_sessions = mysqli_stmt_get_result($stmt_sessions);

// Begin output
$output_sessions = '';

if (mysqli_num_rows($result_sessions) > 0) {
    while ($row = mysqli_fetch_assoc($result_sessions)) {
         $date = new DateTime($row['DATE']);
        $formattedDate = $date->format('M j, Y \a\t g:i A');
        
        //for styling the status
        $statusClass = '';
        switch(strtolower($row['Status'])) {
            case 'scheduled': $statusClass = 'badge bg-info'; break;
            case 'completed': $statusClass = 'badge bg-success'; break;
            case 'cancelled': $statusClass = 'badge bg-danger'; break;
            default: $statusClass = 'badge bg-secondary';
        }
        $statusBadge = "<span class='$statusClass'>{$row['Status']}</span>";
        
        // cancel button (for scheduled sessions only)
        $cancelButton = '';
        if (strtolower($row['Status']) === 'scheduled') {
            $cancelButton = "
                <button class='btn btn-danger btn-sm cancel-btn' 
                        data-id='{$row['Session_ID']}'
                        title='Cancel this session'>
                    Cancel
                </button>
            ";
        }
        $output_sessions .= "
            <tr>
                <td>{$row['FIRST_NAME']} {$row['LAST_NAME']}</td>
                <td>{$formattedDate}</td>
                <td>{$statusBadge}</td>
                <td>{$row['reason']}</td>
                <td>
                <div class='btn-group'>
                    <button class='btn btn-outline-primary btn-sm view-btn' 
                            data-id='{$row['Session_ID']}'
                            title='View session details'>
                        ☐
                    </button>
                    {$cancelButton}
                </div>
            </td>

            </tr>
        ";
    }
} else {
    $output_sessions .= "<tr><td colspan='4' class='text-center text-muted'>No sessions found</td></tr>";
}

echo $output_sessions;


mysqli_stmt_close($stmt_sessions);
mysqli_close($conn);

?>