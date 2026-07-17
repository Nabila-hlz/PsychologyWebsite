// ---------------- SIDEBAR ------------------
    function openSidebar() { document.getElementById("sidebar").style.transform = "translateX(0)"; }
    function closeSidebar() { document.getElementById("sidebar").style.transform = "translateX(-260px)"; }

    // ---------------- STORAGE & DATA ------------------
    const STORAGE_KEY = "innerbloom_data";

    // Default users
    const defaultClients = [
      { id: 1, name: "Admin User", email: "admin@innerbloom.com", password: "admin123", phone: "+213600000001", role: "admin" },
      { id: 2, name: "User1", email: "user1@example.dz", password: "123456", phone: "+213600000002", role: "user" },
      { id: 3, name: "User2", email: "user2@example.dz", password: "123456", phone: "+213600000003", role: "user" },
      { id: 4, name: "User3", email: "user3@example.dz", password: "123456", phone: "+213600000004", role: "user" },
      { id: 5, name: "User4", email: "user4@example.dz", password: "123456", phone: "+213600000005", role: "user" },
      { id: 6, name: "User5", email: "user5@example.dz", password: "123456", phone: "+213600000006", role: "user" },
      { id: 7, name: "User6", email: "user6@example.dz", password: "123456", phone: "+213600000007", role: "user" },
      { id: 8, name: "User7", email: "user7@example.dz", password: "123456", phone: "+213600000008", role: "user" },
      { id: 9, name: "User8", email: "user8@example.dz", password: "123456", phone: "+213600000009", role: "user" },
      { id: 10, name: "User9", email: "user9@example.dz", password: "123456", phone: "+213600000010", role: "user" },
      { id: 11, name: "User10", email: "user10@example.dz", password: "123456", phone: "+213600000011", role: "user" }
    ];

    // Default doctors
    const defaultDoctors = [
      { id: 11, fullName: "Dr. Samir Bensalem", email: "samir.bensalem@example.dz", phone: "+213660200001", password: "123456", role: "doctor", specialty: "therapy", price: "5000 DA", verified: true, createdAt: new Date().toISOString() },
      { id: 1, fullName: "Dr. Samir Bensalem", email: "dr1@example.dz", password: "123456", phone: "+213661000001", specialty: "therapy", verified: true, role: "doctor", price: "5000 DA", createdAt: new Date().toISOString() },
      { id: 2, fullName: "Dr. Amine Salah", email: "dr2@example.dz", password: "123456", phone: "+213661000002", specialty: "Psychology", verified: true, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() },
      { id: 3, fullName: "Dr. Yasmine Cherif", email: "dr3@example.dz", password: "123456", phone: "+213661000003", specialty: "Therapy", verified: false, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() },
      { id: 4, fullName: "Dr. Karim Benaissa", email: "dr4@example.dz", password: "123456", phone: "+213661000004", specialty: "positive psycology", verified: true, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() },
      { id: 5, fullName: "Dr. Laila Hachem", email: "dr5@example.dz", password: "123456", phone: "+213661000005", specialty: "Psychology", verified: false, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() },
      { id: 6, fullName: "Dr. Nadir Belkacem", email: "dr6@example.dz", password: "123456", phone: "+213661000006", specialty: "Therapy", verified: true, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() },
      { id: 7, fullName: "Dr. Faten Meriem", email: "dr7@example.dz", password: "123456", phone: "+213661000007", specialty: "therapy", verified: true, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() },
      { id: 8, fullName: "Dr. Imane Farid", email: "dr8@example.dz", password: "123456", phone: "+213661000008", specialty: "positive psycology", verified: true, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() },
      { id: 9, fullName: "Dr. Ahmed Saadi", email: "dr9@example.dz", password: "123456", phone: "+213661000009", specialty: "Psychology", verified: true, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() },
      { id: 10, fullName: "Dr. Selma Kadi", email: "dr10@example.dz", password: "123456", phone: "+213661000010", specialty: "Therapy", verified: true, price: "5000 DA", role: "doctor", createdAt: new Date().toISOString() }
    ];

(function initializeStorage() {
  let data = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};

  // If clients array is missing or empty, populate default
  if (!data.clients || data.clients.length === 0) data.clients = [...defaultClients];
  if (!data.doctors || data.doctors.length === 0) data.doctors = [...defaultDoctors];
  if (!data.sessions) data.sessions = [];

  localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
})();


    // Initialize storage
   

    function getData() { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || { clients: [], doctors: [], sessions: [] }; }
    function saveData(data) { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); }
    function getClients() { return getData().clients; }
    function saveClients(clients) { const d = getData(); d.clients = clients; saveData(d); }
    function getDoctors() { return getData().doctors; }
    function saveDoctors(doctors) { const d = getData(); d.doctors = doctors; saveData(d); }
    function getSessions() { return getData().sessions; }
    function saveSessions(sessions) { const d = getData(); d.sessions = sessions; saveData(d); }

    const root = document.getElementById("root");

    // ---------------- CARDS & CHARTS ------------------
    function updateCards() {
      document.getElementById("patients-count").textContent = getClients().length;
      document.getElementById("psychologists-count").textContent = getDoctors().length;
      document.getElementById("sessions-count").textContent = getSessions().length;
    }
    updateCards();

    // Charts using ApexCharts
    const clients = getClients();
    const doctors = getDoctors();
    const verifiedCount = doctors.filter(d => d.verified).length;
    const pendingCount = doctors.length - verifiedCount;

    let growthChart = new ApexCharts(document.querySelector("#growth-chart"),{
      chart:{type:"line",height:250},
      series:[{name:"Clients",data:clients.map((_,i)=>i+1)},{name:"Doctors",data:doctors.map((_,i)=>i+1)}],
      xaxis:{categories:Array.from({length:Math.max(clients.length,doctors.length)},(_,i)=>`Week ${i+1}`)},
      stroke:{curve:"smooth",width:3},
      colors:["#6ca574","#1666f0"]
    });
    growthChart.render();

    let verifyChart = new ApexCharts(document.querySelector("#verify-chart"),{
      chart:{type:"donut",height:250},
      series:[verifiedCount,pendingCount],
      labels:["Verified","Pending"],
      colors:["#16c918","#fa3e3e"]
    });
    verifyChart.render();

    // ---------------- SECTION RENDERING ------------------
    function renderOverview() {
      // Hide other sections and show overview
      document.getElementById('overview-section').classList.add('active');
      root.innerHTML = '';
    }

    function renderUsers() {
      // Hide overview and show users section
      document.getElementById('overview-section').classList.remove('active');
      
      const clients = getClients();
      root.innerHTML = `
      <div class="card-clean p-3 mt-4">
        <h5>Users</h5>
        <table class="table mt-3">
          <thead>
            <tr>
              <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            ${clients.map(u => `<tr data-id="${u.id}">
              <td>${u.id}</td><td>${u.name}</td><td>${u.email}</td>
              <td>${u.phone}</td><td>${u.role}</td>
              <td>
                <button class="view-btn btn">View</button>
                <button class="delete-btn btn">Delete</button>
                <button class="role-btn btn">Change Role</button>
              </td>
            </tr>`).join('')}
          </tbody>
        </table>
      </div>
    `;

      document.querySelectorAll(".delete-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = Number(btn.closest("tr").dataset.id);
          if (confirm("Are you sure you want to delete this user?")) {
            saveClients(clients.filter(u => u.id !== id));
            renderUsers();
            updateCards();
          }
        });
      });

      document.querySelectorAll(".role-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = Number(btn.closest("tr").dataset.id);
          const user = clients.find(u => u.id === id);
          const newRole = prompt("Enter role (user/admin):", user.role);
          if (["user", "admin"].includes(newRole)) { 
            user.role = newRole; 
            saveClients(clients); 
            renderUsers(); 
          } else {
            alert("Invalid role!");
          }
        });
      });

      document.querySelectorAll(".view-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = Number(btn.closest("tr").dataset.id);
          const user = clients.find(u => u.id === id);
          alert(`Name: ${user.name}\nEmail: ${user.email}\nPhone: ${user.phone}`);
        });
      });
    }

    function renderDoctors() {
      // Hide overview and show doctors section
      document.getElementById('overview-section').classList.remove('active');
      
      const doctors = getDoctors();
      root.innerHTML = `
      <div class="card-clean p-3 mt-4">
        <h5>Doctors</h5>
        <table class="table mt-3">
          <thead>
            <tr>
              <th>ID</th><th>Full Name</th><th>Email</th><th>Specialty</th><th>Phone</th><th>Verified</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            ${doctors.map((d, i) => `<tr>
              <td>${d.id}</td><td>${d.fullName}</td><td>${d.email}</td>
              <td>${d.specialty}</td><td>${d.phone}</td><td>${d.verified ? 'Yes' : 'No'}</td>
              <td>
                <button class="view-btn btn" data-index="${i}">View</button>
                <button class="delete-btn btn" data-index="${i}">Delete</button>
                <button class="verify-btn btn" data-index="${i}">${d.verified ? 'Unverify' : 'Verify'}</button>
              </td>
            </tr>`).join('')}
          </tbody>
        </table>
      </div>
    `;

      // Attach actions
      document.querySelectorAll(".view-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const index = btn.dataset.index;
          const doc = doctors[index];
          alert(`Name: ${doc.fullName}\nEmail: ${doc.email}\nSpecialty: ${doc.specialty}\nPhone: ${doc.phone}`);
        });
      });

      document.querySelectorAll(".delete-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const index = btn.dataset.index;
          if (confirm("Delete doctor?")) {
            doctors.splice(index, 1);
            saveDoctors(doctors);
            renderDoctors();
            updateCards();
          }
        });
      });

      document.querySelectorAll(".verify-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const index = btn.dataset.index;
          doctors[index].verified = !doctors[index].verified;
          saveDoctors(doctors);
          renderDoctors();
        });
      });
    }

    function renderSection(section) {
      // Clear any previous content in root
      root.innerHTML = '';
      
      switch (section) {
        case "overview": renderOverview(); break;
        case "users": renderUsers(); break;
        case "doctors": renderDoctors(); break;
        case "appointments": 
          document.getElementById('overview-section').classList.remove('active');
          root.innerHTML = "<div class='card-clean p-3 mt-4'><h5>Appointments Section</h5></div>"; 
          break;
        case "reports": 
          document.getElementById('overview-section').classList.remove('active');
          root.innerHTML = "<div class='card-clean p-3 mt-4'><h5>Reports Section</h5></div>"; 
          break;
        case "settings": 
          document.getElementById('overview-section').classList.remove('active');
          root.innerHTML = "<div class='card-clean p-3 mt-4'><h5>Settings Section</h5></div>"; 
          break;
      }
    }

    // ---------------- SIDEBAR NAV ------------------
    document.querySelectorAll('.sidebar-item').forEach(item => {
      item.addEventListener('click', () => {
        document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        renderSection(item.dataset.section);
      });
    });

    // Initial load
    renderSection("overview");




