<?php
session_start();
require_once "dbconnection.php";

if (!isset($_SESSION['user_id'])) {
    //  redirect to login page
    header("Location: ../../assets/index.php");
    exit;
}
$therapist_id=$_SESSION['user_id'];

// Calculate the date six months ago from the current date
$dateSixMonthsAgo = date('Y-m-01', strtotime('-5 months'));

// Generate an array for months
$dates = [];
for ($i = 0; $i < 6; $i++) {
    $dates[] = date('Y-M', strtotime("+$i month", strtotime($dateSixMonthsAgo)));
}

// Prepare the database query
$query = "
SELECT DATE_FORMAT(s.DATE, '%Y-%m') AS session_month, 
       p.FIRST_NAME AS PATIENT_NAME,
       COUNT(s.SESSION_ID) AS SESSION_COUNT
FROM session s
JOIN user p ON s.PATIENT_ID = p.USER_ID
WHERE s.THERAPIST_ID = ? AND s.DATE >= ?
GROUP BY session_month, p.FIRST_NAME
ORDER BY session_month DESC;
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "is", $therapist_id, $dateSixMonthsAgo);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// initializing arrays for monthly data and output
$output = '';
$monthlyData = array_fill_keys($dates, 0); 

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
 $sessionMonth = $row['session_month']; // '2026-01'
        // Make sure to check if the month exists in the array
        if (array_key_exists($sessionMonth, $monthlyData)) {
            $monthlyData[$sessionMonth] += $row['SESSION_COUNT'];
        } else {
            // Optional: Initialize if not found, though it shouldn't happen with the previous setup
            $monthlyData[$sessionMonth] = $row['SESSION_COUNT'];
        }        $output .= "
            <tr>
                <td>{$row['PATIENT_NAME']}</td>
                <td>{$row['SESSION_COUNT']}</td>
                <td>{$row['session_month']}</td>
            </tr>
        ";
    }
} else {
    $output .= "<tr><td colspan='3' class='text-center text-muted'>No sessions in the last six months</td></tr>";
}

// preparing chart labels and data for the JavaScript
$chartLabels = implode(',', array_keys($monthlyData));
$chartData = implode(',', array_values($monthlyData));

// Output the chart data as html attributes
echo "<div class='chart-data' data-labels='$chartLabels' data-data='$chartData' style='display: none;'></div>";
echo $output;

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>