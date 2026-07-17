<?php
session_start();
require_once 'dbconnection.php';

$userRole = $_SESSION['role'] ?? '';
$userName = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InnerBloom – Videos</title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/video.css?v=<?php echo time(); ?>">
</head>
<body>

<header>
  
   <nav class="navbar">
            <div class="logo">
                <img src="../images/logo.png" alt="InnerBloom logo">
            </div>
            <div class="nav-links">
                <a href="../../src/index.php">Home</a>
                <a href="article.php">Articles</a>
                <a href="video.php">Videos</a>
                <a href="../../src/about.html">About us</a>
                <?php if ($userRole === 'admin'): ?>
                    <a href="admin.php">Dashboard</a>
                <?php endif; ?>
                <?php if ($userRole === 'therapist'): ?>
                    <a href="../../src/psychologist.html">Dashboard</a>
                <?php endif; ?>
                <?php if ($userRole === 'patient'): ?>
                    <a href="../../src/user_dashboard.html">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Log Out</a>
            </div>
        </nav>
   
</header>

 <div class="title">
            <h2><i class="fas fa-play-circle"></i> Video Library</h2>
              </div>
<main class="container">

    <?php if ($userRole === 'therapist'): ?>
    <section class="upload-section">
        <div class="section-header">
            <h2><i class="fas fa-video"></i> Add New YouTube Video</h2>
            <p class="section-subtitle">Share educational content with your patients</p>
        </div>

        <form id="addVideoForm" class="upload-form">
            <input type="hidden" name="action" value="add_video">

            <div class="form-group">
                <label for="videoTitle"><i class="fas fa-heading"></i> Video Title</label>
                <input type="text" id="videoTitle" name="title" placeholder="Enter video title" required>
            </div>

            <div class="form-group">
                <label for="videoDesc"><i class="fas fa-align-left"></i> Description</label>
                <textarea id="videoDesc" name="description" placeholder="Brief description of the video content" rows="3" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="videoUrl"><i class="fab fa-youtube"></i> YouTube URL</label>
                    <input type="url" id="videoUrl" name="video_url"
                           placeholder="https://www.youtube.com/watch?v=..."
                           required>
                    <small>Paste any YouTube video link</small>
                </div>

                <div class="form-group">
                    <label for="specialtySelect"><i class="fas fa-tag"></i> Specialty</label>
                    <select name="specialty_id" id="specialtySelect" required>
                        <option value="">Select specialty</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-upload"></i> Publish Video
            </button>
        </form>
    </section>
    <?php endif; ?>

    
    <section class="videos-section">
        <div class="section-header">
           
            <div class="filter-controls">
                <input type="text" id="searchInput" placeholder="Search videos..." class="search-input">
                <select id="filterSpecialty" class="filter-select">
                    <option value="">All Specialties</option>
                </select>
            </div>
        </div>

        <div id="videoGrid" class="video-grid">
            <!-- Loading spinner -->
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading videos...
            </div>
        </div>
    </section>

</main>



  <footer>
        <center>
            <table>
                <tr>
                    <td><img src="../images/footerpic.png" alt="InnerBloom Logo" height="70"></td>
                    <td rowspan="2">
                        <pre>
        Need help ?
        ✆Call us at <a href="tel:+213674674674">+213 674 674 674</a>
        📧mail us <a href="mailto::innerbloomdz@email.com">innerbloomdz@gmail.com</a>
        available Sunday - Thursday, 09:00 am - 06:00pm 
    </pre>
                    </td>
                </tr>
            </table>
        </center>
    </footer>

<script>
const userRole = '<?php echo $userRole; ?>';
let allVideos = [];
let currentVideoId = null;

// Load videos
async function loadVideos() {
    try {
        const res = await fetch('api2.php?action=get_videos');
        const data = await res.json();

        if (data.success) {
            allVideos = data.videos;
            displayVideos(allVideos);
        } else {
            showError('Failed to load videos');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error loading videos');
    }
}

// Display videos
function displayVideos(videos) {
    const grid = document.getElementById('videoGrid');
    
    if (videos.length === 0) {
        grid.innerHTML = '<div class="no-videos"><i class="fas fa-video-slash"></i> No videos found</div>';
        return;
    }

    grid.innerHTML = videos.map(v => `
        <div class="video-card" data-video-id="${v.CONTENT_ID}">
            <div class="video-frame">
                <iframe src="${v.PATH}" 
                        allowfullscreen
                        title="${escapeHtml(v.TITLE)}"></iframe>
            </div>
            <div class="video-info">
                <h4>${escapeHtml(v.TITLE)}</h4>
                <p class="video-doctor"><i class="fas fa-user-md"></i> ${escapeHtml(v.doctor_name)}</p>
                <div class="video-meta">
                    <span class="specialty-badge">${escapeHtml(v.SPECIALTY_NAME || 'General')}</span>
                    <span class="views"><i class="fas fa-eye"></i> ${formatViews(v.VIEWS)}</span>
                </div>
                ${userRole === 'admin' ? `
                <button class="btn-delete-small" onclick="deleteVideoFromGrid(${v.CONTENT_ID}); event.stopPropagation();">
                    <i class="fas fa-trash"></i>
                </button>
                ` : ''}
            </div>
        </div>
    `).join('');
}

// Delete video from grid (admin only)
async function deleteVideoFromGrid(videoId) {
    if (!confirm('Are you sure you want to delete this video? This action cannot be undone.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'delete_video');
        formData.append('video_id', videoId);

        const res = await fetch('api2.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (result.success) {
            showSuccess('Video deleted successfully');
            loadVideos();
        } else {
            showError(result.message || 'Failed to delete video');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error deleting video');
    }
}

// Load specialties
async function loadSpecialties() {
    try {
        const res = await fetch('api2.php?action=get_specialties');
        const data = await res.json();

        if (data.success) {
            const formSelect = document.getElementById('specialtySelect');
            const filterSelect = document.getElementById('filterSpecialty');

            data.specialties.forEach(s => {
                if (formSelect) {
                    formSelect.innerHTML += `<option value="${s.SPECIALTY_ID}">${s.SPECIALTY_NAME}</option>`;
                }
                if (filterSelect) {
                    filterSelect.innerHTML += `<option value="${s.SPECIALTY_ID}">${s.SPECIALTY_NAME}</option>`;
                }
            });
        }
    } catch (error) {
        console.error('Error loading specialties:', error);
    }
}

// Add video form submission
document.getElementById('addVideoForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';
    submitBtn.disabled = true;

    try {
        const formData = new FormData(e.target);

        const res = await fetch('api2.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (result.success) {
            showSuccess(result.message);
            e.target.reset();
            loadVideos();
        } else {
            showError(result.message || 'Failed to add video');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error adding video');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', (e) => {
    filterVideos();
});

// Filter by specialty
document.getElementById('filterSpecialty')?.addEventListener('change', (e) => {
    filterVideos();
});

function filterVideos() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const specialtyId = document.getElementById('filterSpecialty').value;

    let filtered = allVideos;

    if (searchTerm) {
        filtered = filtered.filter(v => 
            v.TITLE.toLowerCase().includes(searchTerm) ||
            v.DISCRIPTION.toLowerCase().includes(searchTerm) ||
            v.doctor_name.toLowerCase().includes(searchTerm)
        );
    }

    if (specialtyId) {
        filtered = filtered.filter(v => v.SPECIALTY_ID == specialtyId);
    }

    displayVideos(filtered);
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatViews(views) {
    if (views >= 1000000) return (views / 1000000).toFixed(1) + 'M';
    if (views >= 1000) return (views / 1000).toFixed(1) + 'K';
    return views;
}

function showSuccess(message) {
    alert('✓ ' + message);
}

function showError(message) {
    alert('✗ ' + message);
}

// Initialize
loadVideos();
loadSpecialties();
</script>


</body>
</html>