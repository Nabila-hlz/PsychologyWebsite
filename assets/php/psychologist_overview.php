<?php
session_start();
require_once "dbconnection.php";

if (!isset($_SESSION['user_id'])) {
    //  redirect to login page
    header("Location: ../../assets/index.php");
    exit;
}
$therapist_id=$_SESSION['user_id'];

// getting therapist info
$therapistQuery = "
    SELECT 
        u.FIRST_NAME, 
        u.LAST_NAME, 
        u.EMAIL,
        CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as name
    FROM user u
    JOIN therapist t ON u.USER_ID = t.THERAPIST_ID
    WHERE u.USER_ID = ?;
";

$stmt = mysqli_prepare($conn, $therapistQuery);
mysqli_stmt_bind_param($stmt, "i", $therapist_id);
mysqli_stmt_execute($stmt);
$therapistResult = mysqli_stmt_get_result($stmt);
$therapist = mysqli_fetch_assoc($therapistResult);

// for today's date
$today = date('Y-m-d');

$todaySessionsQuery = "
    SELECT 
    s.session_id, 
    s.date, 
    s.status,
    s.reason,
    s.meeting_url,
    u.FIRST_NAME,
    u.LAST_NAME,
    CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as patient_name
FROM session s
JOIN user u ON s.PATIENT_ID = u.USER_ID
WHERE s.therapist_id = ?
AND DATE(s.date) = ?
ORDER BY s.date ASC;
";

$todayStmt = mysqli_prepare($conn, $todaySessionsQuery);
mysqli_stmt_bind_param($todayStmt, "is", $therapist_id, $today);
mysqli_stmt_execute($todayStmt);
$todaySessionsResult = mysqli_stmt_get_result($todayStmt);
$todaySessions = mysqli_fetch_all($todaySessionsResult, MYSQLI_ASSOC);

// getting statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN DATE(date) = CURDATE() THEN 1 ELSE 0 END) as today_sessions,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
        SUM(CASE WHEN status = 'scheduled' AND date > NOW() THEN 1 ELSE 0 END) as upcoming_sessions,
        COUNT(DISTINCT patient_id) as total_patients
    FROM session
    WHERE therapist_id = ?
";

$statsStmt = mysqli_prepare($conn, $statsQuery);
mysqli_stmt_bind_param($statsStmt, "i", $therapist_id);
mysqli_stmt_execute($statsStmt);
$statsResult = mysqli_stmt_get_result($statsStmt);
$stats = mysqli_fetch_assoc($statsResult);

$nextSessionQuery = "
    SELECT 
        s.date,
        u.FIRST_NAME,
        u.LAST_NAME,
        CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as patient_name
    FROM session s
    JOIN user u ON s.PATIENT_ID = u.USER_ID
    WHERE s.THERAPIST_ID = ?
    AND s.date > NOW()
    AND s.status = 'scheduled'
    ORDER BY s.date ASC
    LIMIT 1
";

$nextStmt = mysqli_prepare($conn, $nextSessionQuery);
mysqli_stmt_bind_param($nextStmt, "i", $therapist_id);
mysqli_stmt_execute($nextStmt);
$nextResult = mysqli_stmt_get_result($nextStmt);
$nextSession = mysqli_fetch_assoc($nextResult);

// (last 5)
$recentSessionsQuery = "
    SELECT 
        s.SESSION_ID,
        s.date,
        s.status,
        s.meeting_url,
        u.FIRST_NAME,
        u.LAST_NAME,
        CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as patient_name
    FROM session s
    JOIN user u ON s.PATIENT_ID = u.USER_ID
    WHERE s.therapist_id = ?
    ORDER BY s.date DESC
    LIMIT 5
";

$recentStmt = mysqli_prepare($conn, $recentSessionsQuery);
mysqli_stmt_bind_param($recentStmt, "i", $therapist_id);
mysqli_stmt_execute($recentStmt);
$recentResult = mysqli_stmt_get_result($recentStmt);
$recentSessions = mysqli_fetch_all($recentResult, MYSQLI_ASSOC);

// Get session status distribution
$typeQuery = "
    SELECT 
        status,
        COUNT(*) as count
    FROM session
    WHERE therapist_id = ?
    GROUP BY status
    ORDER BY count DESC
";

$typeStmt = mysqli_prepare($conn, $typeQuery);
mysqli_stmt_bind_param($typeStmt, "i", $therapist_id);
mysqli_stmt_execute($typeStmt);
$typeResult = mysqli_stmt_get_result($typeStmt);
$sessionTypes = mysqli_fetch_all($typeResult, MYSQLI_ASSOC);

// helper functions for display
function formatDate($dateString) {
    if (!$dateString) return '—';
    $date = new DateTime($dateString);
    return $date->format('M j, Y \a\t g:i A'); // 2024-12-25 14:30:00 ->  Dec 25, 2024 at 2:30 PM
}
function formatTime($dateString) {
    if (!$dateString) return '';
    $date = new DateTime($dateString);
    return $date->format('g:i A');
}

function getStatusBadge($status) {
    $statusClasses = [
        'scheduled' => 'badge bg-info',
        'completed' => 'badge bg-success',
        'cancelled' => 'badge bg-danger',
        'pending' => 'badge bg-warning'
    ];
    
    $class = $statusClasses[$status] ?? 'badge bg-secondary';
    return "<span class='$class'>" . ucfirst($status) . "</span>";
}
?>

<!-- HTML OUTPUT BEGINS HERE -->
<div class="row g-3">
    <div class="col-12">
        <div class="card-clean d-flex justify-content-between align-items-center p-4">
            <div>
                <h4 style="margin:0; color:green;" ><?php echo htmlspecialchars($therapist['name']); ?></h4>
                <div class="text-muted">Licensed Psychologist • <?php echo htmlspecialchars($therapist['EMAIL']); ?></div>
            </div>
            <div class="d-flex gap-4">
                <div class="text-center">
                    <p class="text-muted mb-1">Next Session</p>
                    <h4 class="text-primary">
                        <?php if ($nextSession): ?>
                            <?php echo htmlspecialchars($nextSession['patient_name']); ?><br>
                            <small class="text-muted"><?php echo formatDate($nextSession['date']); ?></small>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="text-center">
                    <p class="text-muted mb-1">Upcoming</p>
                    <h3 class="text-success"><?php echo $stats['upcoming_sessions'] ?? 0; ?></h3>
                </div>
                <div class="text-center">
                    <p class="text-muted mb-1">Patients</p>
                    <h3 class="text-info"><?php echo $stats['total_patients'] ?? 0; ?></h3>
                </div>
                <div class="text-center">
                    <p class="text-muted mb-1">Completed</p>
                    <h3 class="text-warning"><?php echo $stats['completed_sessions'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-clean p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 style="margin:0">Today's Sessions</h5>
                <span class="badge bg-primary"><?php echo count($todaySessions); ?> sessions</span>
            </div>
            
            <div id="listToday">
                <?php if (empty($todaySessions)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                        <p class="mt-2">No sessions scheduled for today</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($todaySessions as $session): ?>
                    <div class="d-flex align-items-center justify-content-between mb-3 p-3 border-bottom">
                    <div>
                        <div style="font-weight:700"><?php echo htmlspecialchars($session['patient_name']); ?></div>
                        <div class="text-muted">
                            <?php echo formatTime($session['date']); ?>
                        </div>
                    </div>
                    <div>
                        <?php 
                        //VIDEO CALL BUTTON HERE
                        $sessionTime = strtotime($session['date']);
                        $currentTime = time();
                        $timeDiff = ($sessionTime - $currentTime) / 60; // minutes difference
                        
                        if (abs($timeDiff) <= 7 && $session['status'] === 'scheduled') {
                            if (empty($session['meeting_url'])) {
                                echo '<button class="btn btn-sm btn-success start-meeting-btn" 
                                        data-session-id="' . $session['session_id'] . '"
                                        onclick="startVideoCall(' . $session['session_id'] . ')"> 
                                        <i class="fas fa-video"></i> Start Call
                                    </button>';
                            } else {
                                echo '<a href="' . htmlspecialchars($session['meeting_url']) . '" 
                                    target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-video"></i> Join Meeting
                                    </a>';
                            }
                        } else {
                            echo getStatusBadge($session['status']);
                        }
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card-clean p-3">
            <h5 style="margin:0">Recent Sessions</h5>
            <div class="table-responsive mt-3">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Video_Call</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSessions)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No sessions found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentSessions as $session): ?>
                            <tr>
                            <td><?php echo htmlspecialchars($session['patient_name']); ?></td>
                            <td><?php echo formatDate($session['date']); ?></td>                                
                            <td><?php echo getStatusBadge($session['status']); ?></td>
                            <td>
                                <?php 
                                // VIDEO CALL BUTTON AGAIN
                                $sessionTime = strtotime($session['date']);
                                $currentTime = time();
                                $timeDiff = ($sessionTime - $currentTime) / 60; // minutes difference
                                
                                if (abs($timeDiff) <= 7 && $session['status'] === 'scheduled') {
                                    if (empty($session['meeting_url'])) {
                                        echo '<button class="btn btn-sm btn-success start-meeting-btn" 
                                                data-session-id="' . $session['SESSION_ID'] . '"
                                                onclick="startVideoCall(' . $session['SESSION_ID'] . ')"> 
                                                <i class="fas fa-video"></i> Start Call
                                            </button>';
                                    } else {
                                        echo '<a href="' . htmlspecialchars($session['meeting_url']) . '" 
                                            target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-video"></i> Join Meeting
                                            </a>';
                                    }
                                } else {
                                    echo '<span class="text-muted">—</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Session Modal (Hidden by default) -->
<div class="modal fade" id="sessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sessionModalBody">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSessionNotes">Save Notes</button>
            </div>
        </div>
    </div>
</div>

<script>
// preparing chart data from PHP
const sessionTypes = <?php echo json_encode($sessionTypes); ?>;
const typeLabels = sessionTypes.map(t => t.session_type);
const typeData = sessionTypes.map(t => t.count);

// initializing chart when page loads
document.addEventListener('DOMContentLoaded', function() {
    renderTypeChart();
});

function renderTypeChart() {
    const ctx = document.getElementById('typeChart').getContext('2d');
    
    if (chartInst) {
        chartInst.destroy();
    }
    
    chartInst = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeData,
                backgroundColor: ['#1e4d2b', '#7ca07c', '#6b8072', '#679267', '#4a7c59']
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($todayStmt);
mysqli_stmt_close($statsStmt);
mysqli_stmt_close($nextStmt);
mysqli_stmt_close($recentStmt);
mysqli_stmt_close($typeStmt);
mysqli_close($conn);
?>