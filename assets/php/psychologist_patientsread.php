<?php
session_start();
require_once "dbconnection.php";

if (!isset($_SESSION['user_id'])) {
    //  redirect to login page
    header("Location: ../../assets/index.php");
    exit;
}
$therapist_id=$_SESSION['user_id'];

// query for patients
$query = "
SELECT u.USER_ID, 
u.FIRST_NAME, 
u.LAST_NAME, 
u.USERNAME, 
u.EMAIL, 
u.PHONE_NUMBER, 
COUNT(s.SESSION_ID) AS sessions_count, 
MAX(s.DATE) AS last_session
FROM user u 
JOIN session s 
ON u.USER_ID = s.PATIENT_ID 
WHERE s.THERAPIST_ID= ?
GROUP BY u.USER_ID 
ORDER BY last_session DESC;
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $therapist_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$output = '';

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $output .= "
            <tr data-patient-id='{$row['USER_ID']}'>
                <td>{$row['FIRST_NAME']} {$row['LAST_NAME']}</td>
                <td>{$row['EMAIL']}</td>
                <td>{$row['PHONE_NUMBER']}</td>
                <td>{$row['sessions_count']}</td>
                <td>{$row['last_session']}</td>
            </tr>
        ";
    }
} else {
    $output .= "<tr><td colspan='7' class='text-center text-muted'>No patients yet</td></tr>";
}


echo $output;

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>