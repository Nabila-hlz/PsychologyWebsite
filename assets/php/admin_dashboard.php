<?php
session_start();
require_once 'dbconnection.php';

$adminName = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | InnerBloom</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/5.3.5/apexcharts.min.js"></script>
<style>
    :root {
      --mint: #6ca574;
      --muted: #6b7280;
      --bg: #e8f0ea;
      --card: #ffffff;
      --shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
      --radius: 12px;
      --text-dark: #0b1220;
    }
    body { margin:0; font-family: Arial,sans-serif; background: var(--bg); color: var(--text-dark); }
    .header { grid-area: header; background: var(--mint); display:flex; justify-content:space-between; align-items:center; padding:0 25px; color:white; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
    .grid-container { display:grid; grid-template-columns:260px auto; grid-template-rows:70px auto; grid-template-areas:"sidebar header" "sidebar main"; height:100vh; }
    #sidebar { grid-area: sidebar; background: var(--card); padding:25px 0; box-shadow:4px 0 12px rgba(0,0,0,0.1); }
    .sidebar-list { list-style:none; padding:0; margin:20px 0; }
    .sidebar-item { padding:15px 25px; display:flex; align-items:center; gap:15px; font-size:16px; cursor:pointer; text-decoration:none; color:black; }
    .sidebar-item.active { background:#d7eedc; font-weight:600; }
    .main-container { grid-area: main; padding:25px; overflow-y:auto; }
    .cards-container { display:grid; grid-template-columns:repeat(3,1fr); gap:25px; }
    .card { background:white; padding:25px; border-radius:var(--radius); display:flex; justify-content:space-between; align-items:center; box-shadow:var(--shadow); transition: transform .2s ease; }
    .card:hover { transform:translateY(-5px); }
    .charts { display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-top:40px; }
    .chart-card { background:white; padding:25px; border-radius:var(--radius); box-shadow:var(--shadow); }
    .card-clean { background:white; border-radius:var(--radius); box-shadow:var(--shadow); margin-top:25px; }
    .section { display:none; animation: fadeSlide 0.35s ease; }
    .section.active { display:block; }
  .logo img {
    height: 45px;
  }
    @keyframes fadeSlide { from { opacity:0; transform:translateY(12px);} to{opacity:1; transform:translateY(0);} }
</style>
</head>
<body>

<div class="grid-container">

<header class="header">
  <div class="header-right">
    <div class ="logo">
     <img src="../images/logo.png" alt="InnerBloom logo">
</div>
  </div>
</header>

<aside id="sidebar">
  <ul class="sidebar-list">
    <li class="sidebar-item active" data-section="overview">Dashboard</li>
    <li class="sidebar-item" data-section="users">Patients</li>
    <li class="sidebar-item" data-section="doctors">Therapists</li>
    <li class="sidebar-item" data-section="appointments">Appointments</li>
    <a href="video.php" class="sidebar-item">Videos</a>
    <a href="article.php" class="sidebar-item">Articles</a>
    <a href="logout.php" class="sidebar-item">Logout</a>
  </ul>
</aside>

<main class="main-container">

<div id="overview-section" class="section active">
  <div class="cards-container">

  <div class="card">
      <h3>Users</h3>
      <h1 id="usersCount">0</h1>
    </div>
    <div class="card">
      <h3>Patients</h3>
      <h1 id="patientsCount">0</h1>
    </div>
    <div class="card">
      <h3>Therapists</h3>
      <h1 id="psychologistsCount">0</h1>
    </div>
    <div class="card">
      <h3>Appointments</h3>
      <h1 id="sessionsCount">0</h1>
    </div>
    <div class="card">
      <h3>Videos</h3>
      <h1 id="videoCount">0</h1>
    </div>
    <div class="card">
      <h3>Articles</h3>
      <h1 id="articleCount">0</h1>
    </div>

  </div>

  <div class="charts">
    <div class="chart-card">
      <h4>Growth Over Months</h4>
      <div id="growth-chart"></div>
    </div>
    <div class="chart-card">
      <h4>Appointments Over Months</h4>
      <div id="appointments-chart"></div>
    </div>
  </div>
</div>

<div id="root"></div>

</main>
</div>

<script>
let allUsers = [];
let allTherapists = [];
let allAppointments = [];
let gc; // Growth chart instance
let ac; // Appointments chart instance

async function fetchData() {
  try {
    const res = await fetch('admin_operations.php?action=get_dashboard_data');
    const data = await res.json();

    if (!data.success) return;

    allUsers = data.users || [];
    allTherapists = data.therapists || [];
    allAppointments = data.appointments || [];

    document.getElementById('videoCount').textContent = data.videoCount ?? 0;
    document.getElementById('articleCount').textContent = data.articleCount ?? 0;

    updateCards();
    updateCharts();

  } catch (e) {
    console.error(e);
  }
}

function updateCards() {
  document.getElementById('patientsCount').textContent = allUsers.length;
  document.getElementById('psychologistsCount').textContent = allTherapists.length;
  document.getElementById('sessionsCount').textContent = allAppointments.length;
  document.getElementById('usersCount').textContent = allUsers.length +  allTherapists.length;
}

function groupByMonth(arr) {
  const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  const result = {};
  months.forEach(m => result[m] = 0);

  arr.forEach(item => {
    const date = new Date(item.created_at);
    const month = months[date.getMonth()];
    result[month]++;
  });

  return result;
}

function updateCharts() {
  const u = groupByMonth(allUsers);
  const t = groupByMonth(allTherapists);
  const a = groupByMonth(allAppointments);

  // Growth chart
  if (gc) gc.destroy();

  gc = new ApexCharts(document.querySelector("#growth-chart"), {
    chart: { type: 'area', height: 260, toolbar: { show: false } },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    series: [
      { name: 'Users', data: Object.values(u) },
      { name: 'Therapists', data: Object.values(t) }
    ],
    xaxis: { categories: Object.keys(u) },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.1 } },
    colors: ['#6ca574', '#4a90e2']
  });

  gc.render();

  // Appointments chart
  if (ac) ac.destroy();

  ac = new ApexCharts(document.querySelector("#appointments-chart"), {
    chart: { type: 'bar', height: 260, toolbar: { show: false } },
    plotOptions: {
      bar: {
        borderRadius: 8,
        dataLabels: {
          position: 'top',
        },
      }
    },
    dataLabels: {
      enabled: true,
      offsetY: -20,
      style: {
        fontSize: '12px',
        colors: ["#304758"]
      }
    },
    series: [
      { name: 'Appointments', data: Object.values(a) }
    ],
    xaxis: { categories: Object.keys(a) },
    colors: ['#6ca574'],
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'light',
        type: "vertical",
        shadeIntensity: 0.5,
        opacityFrom: 0.85,
        opacityTo: 0.55,
      }
    }
  });

  ac.render();
}

function renderSection(section) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  const root = document.getElementById('root');
  root.innerHTML = '';

  if (section === 'overview') {
    document.getElementById('overview-section').classList.add('active');
    return;
  }

  if (section === 'users') {
    root.innerHTML = `
      <div class="card-clean p-4">
        <h4 class="mb-3">Users</h4>
        <table class="table table-hover">
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone Number</th><th>Gender</th></tr></thead>
          <tbody>
            ${allUsers.map(u => `<tr><td>${u.id}</td><td>${u.first_name} ${u.last_name}</td><td>${u.email}</td><td>${u.phone_number}</td><td>${u.gender}</td></tr>`).join('')}
          </tbody>
        </table>
      </div>
    `;
  }

  if (section === 'doctors') {
    root.innerHTML = `
      <div class="card-clean p-4">
        <h4 class="mb-3">Therapists</h4>
        <table class="table table-hover">
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone Number</th><th>Gender</th></tr></thead>
          <tbody>
            ${allTherapists.map(t => `<tr><td>${t.id}</td><td>${t.name}</td><td>${t.email}</td><td>${t.phone_number}</td><td>${t.gender}</td></tr>`).join('')}
          </tbody>
        </table>
      </div>
    `;
  }



  if (section === 'appointments') {
    root.innerHTML = `
    <div class="card-clean p-3">
        <h4>Appointments</h4>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Date of creation</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            ${allAppointments.map(a => {
                // Determine status color
                let statusColor, borderColor;

              
               if (a.status === 'cancelled') {
                    statusColor = '#dc3545'; // Red
                    borderColor = '#eeb6ba'; // Light red
                } else if (a.status === 'completed') {
                    statusColor = '#0a8d31'; // Orange
                    borderColor = '#ffeeba'; // Light orange
                } else {
                    statusColor = '#6c757d'; // Default gray for unknown status
                    borderColor = '#f8f9fa'; // Light gray
                }

                return `
                <tr>
                  <td>${a.patient_name}</td>
                  <td>${a.therapist_name}</td>
                  <td>${a.created_at}</td>
                  <td>${a.date}</td>
                  <td>
                    <div style="display: inline-block; 
                                 width: 100px; 
                                 height: 40px; 
                                 line-height: 40px;
                                 text-align: center;
                                 border: 2px solid ${borderColor}; 
                                 border-radius: 5px; 
                                 background-color: ${statusColor}; 
                                 color: white;">
                      ${a.status}
                    </div>
                  </td>
                </tr>
                `;
            }).join('')}
          </tbody>
        </table>
      </div>
    `;
}


/*
  if (section === 'appointments') {
    root.innerHTML = `
      <div class="card-clean p-3">
        <h4>Appointments</h4>
        <ul>
          ${allAppointments.map(a => `<li>${a.patient_name} → ${a.therapist_name}</li>`).join('')}
        </ul>
      </div>
    `;
  }*/
}

document.querySelectorAll('.sidebar-item[data-section]').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');
    renderSection(item.dataset.section);
  });
});

fetchData();
</script>

</body>
</html>