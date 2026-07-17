// Global variables
let userRole = null;
let userId = null;
let allVideos = [];
let allSpecialties = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  checkUserSession();
  loadSpecialties();
  loadVideos();
  setupEventListeners();
});

// Check user session and role
function checkUserSession() {
  console.log('Checking user session...'); // Debug
  fetch('../php/check_session.php')
    .then(response => response.json())
    .then(data => {
      console.log('Session data received:', data); // Debug
      if (data.logged_in) {
        userRole = data.role;
        userId = data.user_id;
        console.log('User logged in - Role:', userRole, 'ID:', userId); // Debug
        updateUIBasedOnRole();
      } else {
        console.log('User not logged in, redirecting...'); // Debug
        // Redirect to test login if not logged in
        window.location.href = 'test_login.html';
      }
    })
    .catch(error => {
      console.error('Error checking session:', error);
    });
}

// Update UI based on user role
function updateUIBasedOnRole() {
  console.log('Updating UI for role:', userRole); // Debug
  const addVideoBtn = document.getElementById('addVideoBtn');
  console.log('Add video button element:', addVideoBtn); // Debug
  
  if (userRole === 'therapist' || userRole === 'admin') {
    console.log('Showing add video button for', userRole); // Debug
    addVideoBtn.style.display = 'block';
  } else {
    console.log('Hiding add video button for', userRole); // Debug
    addVideoBtn.style.display = 'none';
  }
}

// Setup event listeners
function setupEventListeners() {
  // Form submission
  document.getElementById('addVideoForm').addEventListener('submit', handleVideoSubmit);
  
  // Video file selection - generate thumbnail
  document.getElementById('videoFile').addEventListener('change', generateThumbnailFromVideo);
  
  // Search on Enter key
  document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      applyFilters();
    }
  });

  // Logout
  document.getElementById('logoutLink').addEventListener('click', function(e) {
    e.preventDefault();
    logout();
  });
}

// Load specialties for dropdown
function loadSpecialties() {
  fetch('../php/video_operations.php?action=get_specialties')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        allSpecialties = data.specialties;
        populateSpecialtyDropdowns();
      }
    })
    .catch(error => console.error('Error loading specialties:', error));
}

// Populate specialty dropdowns (form and filter)
function populateSpecialtyDropdowns() {
  const formSelect = document.getElementById('videoSpecialty');
  const filterSelect = document.getElementById('specialtyFilter');
  
  // Populate form dropdown
  allSpecialties.forEach(specialty => {
    const option = document.createElement('option');
    option.value = specialty.id;
    option.textContent = specialty.name;
    formSelect.appendChild(option);
  });
  
  // Populate filter dropdown
  allSpecialties.forEach(specialty => {
    const option = document.createElement('option');
    option.value = specialty.id;
    option.textContent = specialty.name;
    filterSelect.appendChild(option);
  });
}

// Load videos from database
function loadVideos() {
  fetch('../php/video_operations.php?action=get_videos')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        allVideos = data.videos;
        applyFilters();
      } else {
        showError('Failed to load videos');
      }
    })
    .catch(error => {
      console.error('Error loading videos:', error);
      showError('An error occurred while loading videos');
    });
}

// Apply filters and search
function applyFilters() {
  console.log('applyFilters called'); // Debug
  console.log('Total videos:', allVideos.length); // Debug
  
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const specialtyFilter = document.getElementById('specialtyFilter').value;
  const sortBy = document.getElementById('sortBy').value;
  
  console.log('Search term:', searchTerm); // Debug
  console.log('Specialty filter:', specialtyFilter); // Debug
  console.log('Sort by:', sortBy); // Debug
  
  let filteredVideos = [...allVideos];
  
  // Apply search filter
  if (searchTerm) {
    filteredVideos = filteredVideos.filter(video => 
      video.title.toLowerCase().includes(searchTerm) ||
      video.doctor.toLowerCase().includes(searchTerm) ||
      video.description.toLowerCase().includes(searchTerm) ||
      (video.specialty && video.specialty.toLowerCase().includes(searchTerm))
    );
    console.log('After search filter:', filteredVideos.length); // Debug
  }
  
  // Apply specialty filter
  if (specialtyFilter) {
    filteredVideos = filteredVideos.filter(video => 
      video.specialty_id == specialtyFilter
    );
    console.log('After specialty filter:', filteredVideos.length); // Debug
  }
  
  // Apply sorting
  switch(sortBy) {
    case 'newest':
      filteredVideos.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
    case 'oldest':
      filteredVideos.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
      break;
    case 'mostViewed':
      filteredVideos.sort((a, b) => (b.views || 0) - (a.views || 0));
      break;
    case 'title':
      filteredVideos.sort((a, b) => a.title.localeCompare(b.title));
      break;
  }
  
  console.log('Final filtered videos:', filteredVideos.length); // Debug
  renderVideos(filteredVideos);
}

// Render videos to the page
function renderVideos(videos) {
  const container = document.getElementById('videoContainer');
  container.innerHTML = '';

  if (videos.length === 0) {
    container.innerHTML = '<div class="no-videos"><p>No videos found.</p></div>';
    return;
  }

  videos.forEach(video => {
    const videoDiv = document.createElement('div');
    videoDiv.className = 'video-info';
    
    videoDiv.innerHTML = `
      <div class="thumbnail-container">
        <img src="../${video.thumbnail_path}" alt="${escapeHtml(video.title)}" class="video-thumbnail" onerror="this.src='../assets/images/default-thumbnail.jpg'">
        <div class="play-overlay">
          <span class="material-icons">play_circle_outline</span>
        </div>
      </div>
      
      <div class="video-details">
        <div class="title">${escapeHtml(video.title)}</div>
        
        <div class="therapist">
          <img src="../assets/images/icon.jpg" alt="Doctor icon">
          <span>Dr. ${escapeHtml(video.doctor)}</span>
        </div>
        
        <div class="video-meta">
          ${video.specialty ? `<span class="specialty-tag">${escapeHtml(video.specialty)}</span>` : ''}
          <span class="views"><span class="material-icons">visibility</span> ${formatViews(video.views || 0)}</span>
          <span class="date">${formatDate(video.created_at)}</span>
        </div>
        
        <p class="description">${escapeHtml(truncateText(video.description, 100))}</p>
        
        <div class="video-actions">
          <button class="action-btn view-btn" onclick="viewVideo(${video.id})">
            <span class="material-icons">play_arrow</span> Watch
          </button>
          <a href="../${video.path}" download class="action-btn download-btn">
            <span class="material-icons">download</span> Download
          </a>
          ${userRole === 'admin' ? `<button class="action-btn delete-btn" onclick="deleteVideo(${video.id})"><span class="material-icons">delete</span> Delete</button>` : ''}
        </div>
      </div>
    `;
    
    container.appendChild(videoDiv);
  });
}

// View video in modal
function viewVideo(videoId) {
  const video = allVideos.find(v => v.id === videoId);
  if (!video) return;
  
  // Increment view count
  fetch('../php/video_operations.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `action=increment_views&video_id=${videoId}`
  });
  
  // Create modal
  const modal = document.createElement('div');
  modal.className = 'video-modal';
  modal.innerHTML = `
    <div class="video-modal-content">
      <span class="close-modal" onclick="closeVideoModal()">&times;</span>
      <h2>${escapeHtml(video.title)}</h2>
      <video controls autoplay style="width:100%; max-height:70vh;">
        <source src="../${video.path}" type="video/mp4">
        Your browser does not support the video tag.
      </video>
      <div class="video-info-modal">
        <p><strong>Doctor:</strong> Dr. ${escapeHtml(video.doctor)}</p>
        <p><strong>Specialty:</strong> ${escapeHtml(video.specialty || 'General')}</p>
        <p><strong>Description:</strong> ${escapeHtml(video.description)}</p>
        <p><strong>Views:</strong> ${formatViews(video.views || 0)}</p>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  modal.style.display = 'flex';
}

// Close video modal
function closeVideoModal() {
  const modal = document.querySelector('.video-modal');
  if (modal) {
    modal.remove();
  }
}

// Open add video form
function openForm() {
  document.getElementById('videoForm').style.display = 'flex';
}

// Close add video form
function closeForm() {
  document.getElementById('videoForm').style.display = 'none';
  document.getElementById('addVideoForm').reset();
  document.getElementById('thumbnailData').value = '';
}

// Generate thumbnail from video using HTML5 Canvas
function generateThumbnailFromVideo(e) {
  const file = e.target.files[0];
  if (!file) return;
  
  const video = document.createElement('video');
  const canvas = document.getElementById('thumbnailCanvas');
  const ctx = canvas.getContext('2d');
  
  video.preload = 'metadata';
  video.src = URL.createObjectURL(file);
  
  video.addEventListener('loadeddata', function() {
    // Seek to 2 seconds or 10% of video duration (whichever is smaller)
    const seekTime = Math.min(2, video.duration * 0.1);
    video.currentTime = seekTime;
  });
  
  video.addEventListener('seeked', function() {
    // Set canvas size
    canvas.width = 1280;
    canvas.height = 720;
    
    // Calculate scaling to maintain aspect ratio
    const videoAspect = video.videoWidth / video.videoHeight;
    const canvasAspect = canvas.width / canvas.height;
    
    let drawWidth, drawHeight, offsetX = 0, offsetY = 0;
    
    if (videoAspect > canvasAspect) {
      // Video is wider
      drawWidth = canvas.width;
      drawHeight = canvas.width / videoAspect;
      offsetY = (canvas.height - drawHeight) / 2;
    } else {
      // Video is taller
      drawHeight = canvas.height;
      drawWidth = canvas.height * videoAspect;
      offsetX = (canvas.width - drawWidth) / 2;
    }
    
    // Fill background with black
    ctx.fillStyle = '#000000';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Draw video frame
    ctx.drawImage(video, offsetX, offsetY, drawWidth, drawHeight);
    
    // Convert to base64
    const thumbnailData = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('thumbnailData').value = thumbnailData;
    
    // Clean up
    URL.revokeObjectURL(video.src);
    
    showSuccess('Thumbnail generated successfully!');
  });
  
  video.addEventListener('error', function() {
    showError('Could not generate thumbnail from video');
    URL.revokeObjectURL(video.src);
  });
}

// Handle video form submission
function handleVideoSubmit(e) {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  formData.append('action', 'add_video');
  
  // Show loading
  const submitBtn = e.target.querySelector('.submit-btn');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Uploading...';
  submitBtn.disabled = true;
  
  fetch('../php/video_operations.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showSuccess('Video added successfully!');
      closeForm();
      loadVideos();
    } else {
      showError('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showError('An error occurred while adding the video');
  })
  .finally(() => {
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  });
}

// Delete video
function deleteVideo(videoId) {
  if (!confirm('Are you sure you want to delete this video? This action cannot be undone.')) {
    return;
  }
  
  const formData = new FormData();
  formData.append('action', 'delete_video');
  formData.append('video_id', videoId);
  
  fetch('../php/video_operations.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showSuccess('Video deleted successfully!');
      loadVideos();
    } else {
      showError('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showError('An error occurred while deleting the video');
  });
}

// Logout function
function logout() {
  fetch('../php/logout.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.href = 'login.html';
      }
    })
    .catch(error => {
      console.error('Error logging out:', error);
    });
}

// Utility functions
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function truncateText(text, maxLength) {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}

function formatViews(views) {
  if (views >= 1000000) {
    return (views / 1000000).toFixed(1) + 'M';
  } else if (views >= 1000) {
    return (views / 1000).toFixed(1) + 'K';
  }
  return views;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now - date);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) {
    return 'Today';
  } else if (diffDays === 1) {
    return 'Yesterday';
  } else if (diffDays < 7) {
    return diffDays + ' days ago';
  } else if (diffDays < 30) {
    return Math.floor(diffDays / 7) + ' weeks ago';
  } else if (diffDays < 365) {
    return Math.floor(diffDays / 30) + ' months ago';
  } else {
    return Math.floor(diffDays / 365) + ' years ago';
  }
}

function showSuccess(message) {
  showNotification(message, 'success');
}

function showError(message) {
  showNotification(message, 'error');
}

function showNotification(message, type) {
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  notification.textContent = message;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);
  
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 3000);
}