<?php

session_start();
require_once 'dbconnection.php';

$userRole = $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../src/loginPage.php');
    exit;
}


// Get specialties for dropdown
$specialties = [];
$query = "SELECT SPECIALTY_ID, SPECIALTY_NAME FROM `specialty` ORDER BY SPECIALTY_NAME ";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("SQL Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $specialties[] = $row;
}


// Check for search parameters
$searchKeyword = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? 'all';

// Build the query dynamically
$query = "SELECT 
    a.CONTENT_ID, 
    a.TITLE, 
    a.DISCRIPTION, 
    a.PATH, 
    s.SPECIALTY_NAME, 
    a.CREATED_AT, 
    u.USERNAME AS therapist_name, 
    a.VIEWS,
    a.THUMBNAIL_PATH
FROM CONTENT a
JOIN SPECIALTY s ON a.SPECIALTY_ID = s.SPECIALTY_ID
JOIN USER u ON a.THERAPIST_ID = u.USER_ID
WHERE a.TYPE = 'Article'";

// Add search conditions (only if there's an active search from GET)
if (!empty($searchKeyword)) {
    $searchParam = "%" . $searchKeyword . "%";
    $query .= " AND (a.TITLE LIKE ? OR a.DISCRIPTION LIKE ? OR u.USERNAME LIKE ?)";
}

// Add category filter (only if there's an active search from GET)
if ($categoryFilter !== 'all') {
    $query .= " AND a.SPECIALTY_ID = ?";
}

$query .= " ORDER BY a.CONTENT_ID DESC";

$stmt = $conn->prepare($query);

// Bind parameters if needed
if (!empty($searchKeyword) && $categoryFilter !== 'all') {
    $stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $categoryFilter);
} elseif (!empty($searchKeyword)) {
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
} elseif ($categoryFilter !== 'all') {
    $stmt->bind_param("s", $categoryFilter);
}

$stmt->execute();
$result = $stmt->get_result();

$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles | InnerBloom</title>

    <link rel="stylesheet" href="../css/article.css">
    <link rel="icon" type="x-icon" href="../images/logoNo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <img src="../images/logo.png" alt="InnerBloom logo">
            </div>
            <div class="nav-links">
                <a href="../../src/index.php">Home</a>
                <a href="article.php" class="articles-btn">Articles</a>
                <a href="video.php" class="videos-btn">Videos</a>
                <a href="../../src/about.html">About us</a>
                <a href="#" id="dashboard-link" class="dashboard-btn" style="display: none;">Dashboard</a>
                <a href="logout.php" target="_top" style="text-decoration: none; color: #2e7d32;"> Logout</a>
                
            </div>
        </nav>
    </header>

    <main>
        <div class="title">
            <i class="article-icon fa-solid fa-newspaper"></i>
            <h1>Psychology Articles Library </h1>
        </div>

        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <form method="GET" action="" id="searchForm" class="search-form">
                <input type="text" id="searchInput" name="search" placeholder="Search ... "
                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <select name="category" id="filterSelect">
                    <option value="all">All Categories</option>
                    <?php foreach ($specialties as $specialty): ?>
                        <option value="<?php echo $specialty['SPECIALTY_ID']; ?>"
                            <?php echo (isset($_GET['category']) && $_GET['category'] == $specialty['SPECIALTY_ID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($specialty['SPECIALTY_NAME']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" id="searchBtn">Search</button>
            </form>
        </div>

        <!-- ADD ARTICLE BUTTON (only for admin/therapist) -->
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'therapist')): ?>
            <div class="add-article-btn-container">
                <button class="add-article-btn" id="addArticleBtn">Add Article</button>
            </div>
            <!-- POPUP FORM FOR ADDING ARTICLE -->
            <div id="articleModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <br>
                    <div class="wrapper">
                        <h3>Add New Article</h3>
                        <form id="articleForm" enctype="multipart/form-data" action="addarticle.php:" method="post">
                            <div class="form-group">
                                <label for="articleTitle">Title:</label>
                                <input type="text" id="articleTitle" name="title" required>
                            </div>

                            <div class="form-group">
                                <label for="articleDescription">Description:</label>
                                <textarea id="articleDescription" name="description" rows="3" required></textarea>
                            </div>

                            <div class="form-group file-group">
                                <label for="articleFile">Upload PDF File:</label>
                                <div class="file-input-wrapper">
                                    <span class="file-btn">Choose File</span>
                                    <input type="file" id="articleFile" name="articleFile" accept=".pdf" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="articleThumbnail">Upload Thumbnail:</label>
                                <div class="file-input-wrapper">
                                    <span class="file-btn">Choose an image</span>
                                    <input type="file" id="articleThumbnail" name="articleThumbnail" accept="image/*" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="specialtySelect">Specialty:</label>
                                <select id="specialtySelect" name="specialty_id" required>
                                    <option value="">Select a specialty</option>
                                    <?php foreach ($specialties as $specialty): ?>
                                        <option value="<?php echo $specialty['SPECIALTY_ID']; ?>">
                                            <?php echo htmlspecialchars($specialty['SPECIALTY_NAME']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="submit-btn">Upload Article</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ARTICLES CONTAINER -->
        <div class="containers">
            <?php if (!empty($articles)): ?>
                <?php foreach ($articles as $article): ?>
                    <div class="article-box">

                        <!-- Article Image -->
                        <img src="<?php echo ('../../' . htmlspecialchars($article['THUMBNAIL_PATH'])); ?>" alt="Article Image">

                        <!-- Article Metadata -->
                        <div class="article-metadata">
                            <h2><?php echo htmlspecialchars($article['TITLE']); ?></h2>
                            <span><?php echo htmlspecialchars($article['DISCRIPTION']); ?></span>

                            <?php if (!empty($article['SPECIALTY_NAME'])): ?>
                                <div class="article-category">
                                    <span><?php echo htmlspecialchars($article['SPECIALTY_NAME']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($article['therapist_name'])): ?>
                                <span class="meta-item"><strong>Therapist:</strong> <?php echo htmlspecialchars($article['therapist_name']); ?></span>
                            <?php endif; ?>

                            <?php if (!empty($article['CREATED_AT'])): ?>
                                <span class="meta-item"><strong>Date:</strong> <?php echo date('F j, Y', strtotime($article['CREATED_AT'])); ?></span>
                            <?php endif; ?>

                            <?php if (isset($article['VIEWS'])): ?>
                                <span class="meta-item"><strong>Views:</strong> <?php echo intval($article['VIEWS']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Read More -->
                        <div class="read-more">
                            <a href="view.php?id=<?php echo $article['CONTENT_ID']; ?>" target="_blank">Read More</a>
                        </div>
                    </div>

                <?php endforeach; ?>

            <?php else: ?>
                <p style="text-align:center; width:100%;">No articles found.</p>
            <?php endif; ?>
        </div>


    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="../images/footerpic.png" alt="InnerBloom Logo" height="70">
            </div>
            <div class="footer-contact">
                <p>Need help?</p>
                <p>✆ Call us at +213 674 113 586</p>
                <p>📧 <a href="https://mail.google.com/mail/?view=cm&to=InnerBloom@gmail.com" target="_blank"
                        style="color: rgb(2, 97, 18) ;">innerbloomdz@gmail.com</a></p>
                <p>Available Sunday - Thursday, 09:00 am - 06:00 pm</p>
            </div>
        </div>
    </footer>

    <script>
        //open modal and close it 
        document.addEventListener("DOMContentLoaded", () => {
            const addBtn = document.getElementById("addArticleBtn");
            const modal = document.getElementById("articleModal");
            const closeBtn = modal ? modal.querySelector(".close") : null;
            const form = document.getElementById("articleForm");

            // If button doesn't exist → do NOTHING
            if (!addBtn || !modal) return;

            // Open modal
            addBtn.addEventListener("click", () => {
                modal.style.display = "block";
            });

            // Close modal (X)
            closeBtn.addEventListener("click", () => {
                modal.style.display = "none";
            });

            // Close modal when clicking outside
            window.addEventListener("click", (e) => {
                if (e.target === modal) {
                    modal.style.display = "none";
                    form.reset();
                }
            });

            function handleFileInput(input) {
                const wrapper = input.closest('.file-input-wrapper');
                const btn = wrapper.querySelector('.file-btn');

                input.addEventListener('change', () => {
                    const fileName = input.files[0]?.name || btn.getAttribute('data-default') || 'Choose File';
                    btn.textContent = fileName;
                });
            }

            // PDF input
            const fileInput = document.getElementById('articleFile');
            if (fileInput) {
                fileInput.setAttribute('data-default', 'Choose File'); // default text
                handleFileInput(fileInput);
            }

            // Thumbnail input
            const thumbnailInput = document.getElementById('articleThumbnail');
            if (thumbnailInput) {
                thumbnailInput.setAttribute('data-default', 'Choose an image'); // default text
                handleFileInput(thumbnailInput);
            }


            // --- Form submission listener ---
            if (form) {
                form.addEventListener("submit", async (e) => {
                    e.preventDefault(); // Prevent default form submission

                    const formData = new FormData(form);

                    try {
                        const response = await fetch("addarticle.php", {
                            method: "POST",
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert(result.message || "Article uploaded successfully!");
                            modal.style.display = "none";
                            form.reset();

                            // Optionally reload the articles dynamically
                            window.location.reload(); // simplest way
                        } else {
                            alert(result.message || "Something went wrong.");
                        }

                    } catch (err) {
                        console.error("Error submitting form:", err);
                        alert("An unexpected error occurred. Please try again.");
                    }
                });
            }

            // Handleer search form submission
            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {

                });
            }
            // Add this at the top of your JavaScript
            if (window.performance.navigation.type === 1) { //  1 means page was refreshed
                // Check if we're coming from a search
                const urlParams = new URLSearchParams(window.location.search);
                const hasSearch = urlParams.has('search') || (urlParams.has('category') && urlParams.get('category') !== 'all');

                if (hasSearch && !sessionStorage.getItem('keepSearch')) {
                    // Clear the URL but keep the page
                    window.history.replaceState({}, document.title, window.location.pathname);
                    // Clear form fields
                    document.getElementById('searchInput').value = '';
                    document.getElementById('filterSelect').value = 'all';
                }
            }

            // When user searches, set a flag to keep it on next refresh
            document.getElementById('searchForm').addEventListener('submit', function() {
                sessionStorage.setItem('keepSearch', 'true');
            });

            // Clear the flag when page loads
            window.addEventListener('load', function() {
                sessionStorage.removeItem('keepSearch');
            });

        });
    </script>
    <script src="../assets/php/role.php"></script>
    <script>
        console.log("Role:", userRole, "ID:", userId, "Logged:", loggedIn);

        if (userId && userRole) {
            let dashboardUrl = '';
            switch (userRole) {
                case 'admin':
                    dashboardUrl = '../assets/php/admin_dashboard.php';
                    break;
                case 'therapist':
                    dashboardUrl = '../src/psychologist.html';
                    break;
                case 'user':
                    dashboardUrl = '../src/user_dashboard.php';
                    break;
                default:
                    dashboardUrl = '../src/loginPage.php';
                    break;
            }

            const dashboardLink = document.getElementById('dashboard-link');
            dashboardLink.href = dashboardUrl;
            dashboardLink.style.display = 'inline';
        } else exit;
    </script>
</body>

</html>