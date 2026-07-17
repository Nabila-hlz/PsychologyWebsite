const STORAGE_KEY = 'psychologist_data_v1';
const CURRENT_KEY = 'psychologist_current_user';

// stat
let CURRENT = JSON.parse(localStorage.getItem(CURRENT_KEY));
let chartInst = null;

// ui refs
const root = document.getElementById('root');
const search = document.getElementById('search');


// nav handlers
document.querySelectorAll('.nav-link.clean').forEach(a=>{
  a.addEventListener('click', e=>{
    e.preventDefault();
    document.querySelectorAll('.nav-link.clean').forEach(n=>n.classList.remove('active'));
    a.classList.add('active');
    renderSection(a.dataset.section);
  });
});

/* logout
document.getElementById('logout').addEventListener('click', e=>{
  e.preventDefault();
  localStorage.removeItem(CURRENT_KEY);
  alert('Logged out !');
  location.reload();
});*/

search.addEventListener('input', ()=> {
  const active = document.querySelector('.nav-link.clean.active').dataset.section;
  renderSection(active);
});

function fDate(dt) {
  const d = new Date(dt);
  return d.toLocaleDateString('en-US', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
}

// DASHBOARD OVERVIEW
function renderOverview() {
    console.log("✅ renderOverview() called");

    const root = document.getElementById('root');
    if (!root) {
        console.error("❌ #root element not found!");
        return;
    }
    
    console.log("📦 #root found, loading overview...");
    
    // loading
    root.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading dashboard...</p>
        </div>
    `;
    
    // fetching HTML from PHP
    fetch('../assets/php/psychologist_overview.php', {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(html => {
        // inject the HTML
        root.innerHTML = html;
        
        setTimeout(() => {
            initDashboardComponents();
        }, 100);
    })
    .catch(error => {
        console.error('Error loading dashboard:', error);
        root.innerHTML = `
            <div class="alert alert-danger">
                <strong>Error:</strong> ${error.message}
                <button class="btn btn-sm btn-primary mt-2" onclick="renderOverview()">Retry</button>
            </div>
        `;
    });
}
renderSection('overview');

// helper to initialize dashboard components
function initDashboardComponents() {
    //del after 
    console.log("Initializing dashboard components...");
    // Check if buttons exist
    const buttons = document.querySelectorAll('.start-meeting-btn');
    console.log("Found start-meeting buttons:", buttons.length);

     buttons.forEach(btn => {
        console.log("Adding handler to button with session ID:", btn.getAttribute('data-session-id'));
        
        // Remove any existing listeners first
        btn.removeEventListener('click', handleMeetingClick);
        
        // Add new listener
        btn.addEventListener('click', handleMeetingClick);
    });

    // to initialize any charts or interactive elements
    if (typeof initChart === 'function') {
        initChart();
    }
    
    // click handlers for session buttons
    document.querySelectorAll('.edit-session-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-id');
            openSessionModal(sessionId);
        });
    });
}
function handleMeetingClick() {
    const sessionId = this.getAttribute('data-session-id');
    console.log("Button clicked for session:", sessionId);
    startVideoCall(sessionId);
}

function startVideoCall(sessionId) {
    console.log("startVideoCall called with sessionId:", sessionId);
     // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../assets/php/psychologist_meet.php';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'session_id';
    input.value = sessionId;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    
    // Show loading immediately
    const btn = document.querySelector(`[data-session-id="${sessionId}"]`);
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting...';
    btn.disabled = true;
}

function copyMeetingLink(link) {
    navigator.clipboard.writeText(link).then(() => {
        showNotification('Meeting link copied to clipboard!', 'info');
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function renderPatients() {
  // Show loading indicator
  root.innerHTML = '<div class="text-center p-4"><div class="spinner-border"></div> Loading patients...</div>';
  
  fetch('../assets/php/psychologist_patientsread.php', {
      method: 'GET',
      credentials: 'include'
  })
  .then(response => {
      if (!response.ok) {
          throw new Error('Network response was not ok');
      }
      return response.text(); // Fetching HTML response
  })
  .then(html => {
      // Render the full patients view
      root.innerHTML = `
          <div class="card-clean p-3">
              <div class="d-flex justify-content-between mb-3">
                  <h5 style="margin:0">Patients</h5>
              </div>
              <input type="text" id="patientSearch" placeholder="Search patients..." class="form-control mb-3">
              <div class="table-responsive">
                  <table class="table align-middle" id="patientsTable">
                      <thead>
                          <tr>
                              <th>Name</th>
                              <th>Email</th>
                              <th>Phone</th>
                              <th>Sessions</th>
                              <th>Last Session</th>
                          </tr>
                      </thead>
                      <tbody id="patientsTableBody">
                          ${html}
                      </tbody>
                  </table>
              </div>
          </div>
      `;

      // Add search functionality
      document.getElementById('patientSearch').addEventListener('input', function(e) {
          const searchTerm = e.target.value.toLowerCase();
          const rows = document.querySelectorAll('#patientsTableBody tr');
          
          rows.forEach(row => {
              const text = row.textContent.toLowerCase();
              row.style.display = text.includes(searchTerm) ? '' : 'none';
          });
      });
  })
  .catch(error => {
      console.error('Error fetching patients:', error);
      root.innerHTML = `
          <div class="alert alert-danger">
              <strong>Error:</strong> ${error.message}
              <br>
              <button class="btn btn-sm btn-outline-danger mt-2" onclick="renderPatients()">Retry</button>
          </div>
      `;
  });
}

function renderSessions() {
    // Show loading indicator
    root.innerHTML = '<div class="text-center p-4"><div class="spinner-border"></div> Loading sessions...</div>';

    fetch('../assets/php/psychologist_sessionsread.php', {
        method: 'GET',
        credentials: 'include' 
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(html => {
        root.innerHTML = `
            <div class="card-clean p-3">
                <h5>Sessions</h5>
                <div class="table-responsive">
                    <table class="table align-middle" id="sessionsTable">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Session Date</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="sessionsTableBody">
                            ${html}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                const sessionId = this.getAttribute('data-id');
                cancelSession(sessionId);
            });
        });
    })
    .catch(error => {
        console.error('Error fetching sessions:', error);
        root.innerHTML = `
            <div class="alert alert-danger">
                <strong>Error:</strong> ${error.message}
                <br>
                <button class="btn btn-sm btn-outline-danger mt-2" onclick="renderSessions()">Retry</button>
            </div>
        `;
    });
}
function cancelSession(sessionId) {
   if (!confirm('Are you sure you want to cancel this session? This will notify the patient.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('session_id', sessionId);
    
    fetch('../assets/php/psychologist_sessionsread.php', {
        method: 'POST',
        credentials: 'include',
        body: formData  
    })
    .then(response => response.text())
    .then(result => {
        alert(result);
        renderSessions();
    })
    .catch(error => {
        console.error('Error cancelling session:', error);
        alert('Failed to cancel session: ' + error.message);
    });
}



function renderReports() {
    let chartInsta;
  fetch('../assets/php/psychologist_report.php', { 
      method: 'GET', 
      credentials: 'include' 
  })
  .then(response => {
      if (!response.ok) {
          throw new Error('Network response was not ok');
      }
      return response.text(); // Expecting HTML response
  })
  .then(html => {
      root.innerHTML = `
          <div class="row g-3">
              <div class="col-md-6">
                  <div class="card-clean p-3">
                      <h6 style="margin:0">Monthly Sessions</h6>
                      <canvas id="monthlyChart" height="200" class="mt-3"></canvas>
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="card-clean p-3">
                      <h6 style="margin:0">Patient Progress</h6>
                      <div class="mt-3">
                          <table>
                              <thead>
                                  <tr>
                                      <th> Patient </th>
                                      <th> Session Count </th>
                                      <th> Date </th>
                                  </tr>
                              </thead>
                              <tbody>
                                  ${html} <!-- Insert the HTML from PHP directly -->
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
          </div>
      `;

      // Extract chart data from the HTML
      const chartDataDiv = document.querySelector('.chart-data');

      // Ensure chartDataDiv exists
      if (chartDataDiv) {
          const labels = chartDataDiv.getAttribute('data-labels').split(',');
          const data = chartDataDiv.getAttribute('data-data').split(',').map(Number); // Convert to numbers

          const ctx = document.getElementById('monthlyChart').getContext('2d');

        if (chartInsta) { 
            chartInsta.destroy();
        }          

          chartInsta = new Chart(ctx, {
              type: 'bar',
              data: {
                  labels: labels,
                  datasets: [{
                      label: 'Sessions',
                      data: data,
                      backgroundColor: 'rgba(109,138,170,0.8)',
                      borderColor: 'rgba(109,138,170,1)',
                      borderWidth: 1
                  }]
              },
              options: {
                  plugins: { legend: { display: true } },
                  scales: { y: { beginAtZero: true } }
              }
          });
      }
  })
  .catch(error => {
      console.error('Error fetching session reports:', error);
  });
}

//modal functions
const modalEl = document.getElementById('modalSession'); 
const bsModal = new bootstrap.Modal(modalEl);

function openModal(id){
  document.getElementById('formSession').reset();
  document.getElementById('sessionId').value = id ? id : '';
  document.getElementById('modalTitle').textContent = id ? 'Edit Session' : 'New Session';
  
  //populate patient dropdown
  const patientSelect = document.getElementById('patient');
  patientSelect.innerHTML = DATA.patients.map(p=> `<option value="${p.id}">${p.name}</option>`).join('');
  
  if (id){
    const session = DATA.sessions.find(s=> s.id === parseInt(id));
    if (session){ 
      document.getElementById('patient').value = session.patientId; 
      document.getElementById('sessionType').value = session.type; 
      document.getElementById('notes').value = session.notes || ''; 
      document.getElementById('datetime').value = new Date(session.datetime).toISOString().slice(0,16); 
    }
  }
  bsModal.show();
}

function openPatientModal(){
  // patient modal 
  const name = prompt("Patient name:");
  const email = prompt("Patient email:");
  if (name && email) {
    const newPatient = {
      id: Date.now(),
      name,
      email,
      phone: '',
      notes: ''
    };
    DATA.patients.push(newPatient);
    saveData(DATA);
    renderSection('patients');
  }
}

function viewPatient(patientId){
  const patient = DATA.patients.find(p=> p.id === patientId);
  const sessions = DATA.sessions.filter(s=> s.patientId === patientId && s.psychologistEmail === CURRENT.email);
  
  root.innerHTML = `
    <div class="card-clean p-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="margin:0">${patient.name}</h4>
        <button class="btn btn-sm btn-outline-secondary" onclick="renderSection('patients')">Back</button>
      </div>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Email:</strong> ${patient.email}</p>
          <p><strong>Phone:</strong> ${patient.phone || '—'}</p>
          <p><strong>Notes:</strong> ${patient.notes || '—'}</p>
        </div>
        <div class="col-md-6">
          <h6>Session History</h6>
          ${sessions.length === 0 ? '<p class="muted">No sessions yet</p>' : 
            sessions.map(s=> `
              <div class="mb-2 p-2 border rounded">
                <strong>${fDate(s.datetime)}</strong> - ${s.type}<br>
                <small class="muted">${s.notes || 'No notes'}</small>
              </div>
            `).join('')}
        </div>
      </div>
    </div>
  `;
}

function renderProfile() {
    const root = document.getElementById('root');
    
    // Show loading indicator
    root.innerHTML = '<div class="text-center p-4"><div class="spinner-border"></div> Loading profile...</div>';

    fetch('../assets/php/psychologist_profile.php', {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(html => {
        // Render profile HTML
        root.innerHTML = html;
        
        // Add event listener to the form
        const form = document.getElementById('profileForm');
        if (form) {
            form.addEventListener('submit', updateProfile);
        }
    })
    .catch(error => {
        console.error('Error fetching profile:', error.message);
        root.innerHTML = `
            <div class="alert alert-danger">
                <strong>Error:</strong> ${error.message}
                <br>
                <button class="btn btn-sm btn-outline-danger mt-2" onclick="renderProfile()">Retry</button>
            </div>
        `;
    });
}

// Function to update only the bio
async function updateProfile(event) {
    event.preventDefault();
    
    const bio = document.getElementById('bio').value.trim();
    
    if (!bio) {
        alert('Bio is required');
        return;
    }
    
    // Show loading spinner
    const submitBtn = event ? event.target.querySelector('button[type="submit"]') : 
                             document.querySelector('#profileForm button[type="submit"]');
    const spinner = document.getElementById('saveSpinner');
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    }
    if (spinner) spinner.classList.remove('d-none');
    
    try {
         console.log("Sending request to update bio:", bio);
        const response = await fetch('../assets/php/psychologist_profile_bio.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include', 
            body: JSON.stringify({ bio: bio })
        });
        console.log("Response status:", response.status);
        console.log("Response headers:", [...response.headers]);
        
        
       
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log("Response data:", result);

        if (result.success) {
            showAlert('success', 'Bio updated successfully!');
            
            // to refresh the profile after a delay
            setTimeout(() => {
                renderProfile();
            }, 1500);
            
        } else {
            throw new Error(result.error || 'Failed to update bio');
        }
        
    } catch (error) {
         console.error('Full error details:', {
            message: error.message,
            stack: error.stack,
            bio: bio
        });
        console.error('Error updating profile:', error);
        showAlert('danger', `Error: ${error.message}`);
        
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Update Bio';
        }
        if (spinner) spinner.classList.add('d-none');
    }
}

// Helper function to show alerts
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the root
    const root = document.getElementById('root');
    if (root) {
        root.insertBefore(alertDiv, root.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

function renderSection(section) {
        if (!section) section = 'overview';
        
        if (section === 'overview') renderOverview();
        else if (section === 'patients') renderPatients();
        else if (section === 'sessions') renderSessions();
        else if (section === 'reports') renderReports();
        else if (section === 'profile') renderProfile();
    }

// Helpers
function escapeHtml(s){ if(!s) return ''; return s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;'); }


// Global functions
window.openModal = openModal;
window.viewPatient = viewPatient;
window.renderSection = renderSection;



document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (menuToggle && sidebar && overlay) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    } else {
        console.error('One or more elements not found, check your HTML.');
    }
});


