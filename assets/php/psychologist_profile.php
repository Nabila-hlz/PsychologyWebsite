<?php

session_start();
require_once "dbconnection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../assets/index.php");
    exit;
}
$therapist_id=$_SESSION['user_id'];


$query = "
    SELECT 
        u.FIRST_NAME, 
        u.LAST_NAME, 
        u.USERNAME, 
        u.EMAIL,
        u.PHONE_NUMBER,
        t.SESSION_PRICE, 
        t.BIO,
        GROUP_CONCAT(DISTINCT s.SPECIALTY_NAME ORDER BY s.SPECIALTY_NAME SEPARATOR ', ') AS SPECIALTIES
    FROM user u
    JOIN therapist t ON u.USER_ID = t.THERAPIST_ID
    LEFT JOIN specialty_therapist st ON t.THERAPIST_ID = st.THERAPIST_ID
    LEFT JOIN specialty s ON st.SPECIALTY_ID = s.SPECIALTY_ID
    WHERE u.USER_ID = ?
    GROUP BY u.USER_ID
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $therapist_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$therapist = mysqli_fetch_assoc($result);

if (!$therapist) {
    echo "<div class='alert alert-warning'>Profile not found.</div>";
    exit;
}
?>

<div class="card-clean p-4">
    <h3 class="mb-4">My Profile</h3>
    
    <form id="profileForm" onsubmit="updateProfile(event)">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" 
                       value="<?php echo htmlspecialchars($therapist['FIRST_NAME']); ?>" 
                       readonly disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" 
                       value="<?php echo htmlspecialchars($therapist['LAST_NAME']); ?>" 
                       readonly disabled>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" 
                       value="<?php echo htmlspecialchars($therapist['USERNAME']); ?>" 
                       readonly disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" 
                       value="<?php echo htmlspecialchars($therapist['EMAIL']); ?>" 
                       readonly disabled>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="tel" class="form-control" 
                       value="<?php echo htmlspecialchars($therapist['PHONE_NUMBER']); ?>" 
                       readonly disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Price Per Session</label>
                <div class="input-group">
                    <span class="input-group-text">DA</span>
                    <input type="text" class="form-control" 
                           value="<?php echo htmlspecialchars($therapist['SESSION_PRICE']); ?>" 
                           readonly disabled>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Specialties</label>
            <div class="p-3 bg-light rounded">
                <?php if (!empty($therapist['SPECIALTIES'])): ?>
                    <?php echo htmlspecialchars($therapist['SPECIALTIES']); ?>
                <?php else: ?>
                    <span class="text-muted">No specialties selected</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="bio" class="form-label">Bio *</label>
            <textarea class="form-control" id="bio" name="bio" rows="5" required 
                      placeholder="Tell patients about your background, approach, and expertise..."><?php 
                echo htmlspecialchars($therapist['BIO']); 
            ?></textarea>
            <small class="text-muted">Your bio helps patients understand your approach.</small>
        </div>
        
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" style="background-color:green;">
                <span class="spinner-border spinner-border-sm d-none" id="saveSpinner"></span>
                Update Bio
            </button>
        </div>
        
        <input type="hidden" name="therapist_id" value="<?php echo $therapist_id; ?>">
    </form>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

