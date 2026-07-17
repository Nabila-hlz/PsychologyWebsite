<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InnerBloom</title>
  <link rel="icon" type="image/png" href="../assets/images/logoNo.png">
  <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="logo">
    <img src="../assets/images/logo.png" alt="InnerBloom logo">
  </div>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="therapist.php">Therapist</a>
    <a href="about.html">About us</a>
    <a href="../assets/php/login.php">Log In</a>
    <a href="SignupPage.php"> Sign up</a>
  </div>
  <div class="hamburger" onclick="toggleMenu()">
    <span></span>
    <span></span>
    <span></span>
  </div>
</nav>

<!-- HERO -->
<div class="hero"></div>

<div class="box">

  <!-- FEATURES -->
  <div class="box-container">
    <div class="box-item">
      <p class="before">> Online sessions</p>
      <div class="after">
        Book online therapy sessions with licensed psychologists — flexible, private, and designed to help you feel better.
      </div>
    </div>
    <div class="box-item">
      <p class="before">> Watch related videos</p>
      <div class="after">
        Watch short, practical videos from professional psychologists.
      </div>
    </div>
    <div class="box-item">
      <p class="before">> Read articles</p>
      <div class="after">
        Explore insightful psychology articles and techniques.
      </div>
    </div>
  </div>

  <!-- TEAM -->
  <div class="red-box">
    <div class="textbox1">Our Team of Experts</div>
    <div class="textbox2">
      Meet our licensed psychologists and therapists.
    </div>
  </div>

  <!-- CAROUSEL -->
  <div class="carousel">
    <div class="group" id="therapistGroup"></div>
  </div>

  <!-- REVIEWS -->
  <div class="back">
    <div class="reviews-slider">
      <div class="reviews-container" id="reviewsSlider">
        <!-- Reviews will be loaded here -->
      </div>
      <div class="reviews-dots" id="reviewsDots"></div>
    </div>
  </div>

</div>

<!-- FOOTER -->

  <footer>
        <center>
            <table>
                <tr>
                    <td><img src="../assets/images/footerpic.png" alt="InnerBloom Logo" height="70"></td>
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
/* =============================
   MOBILE MENU TOGGLE
============================= */
function toggleMenu() {
  const navLinks = document.querySelector('.nav-links');
  navLinks.classList.toggle('active');
}


/* =============================
   THERAPISTS CAROUSEL
============================= */
document.addEventListener('DOMContentLoaded', () => {
  const group = document.getElementById('therapistGroup');

  // placeholders
  group.innerHTML = `
    <div class="card empty"><p>Loading...</p></div>
    <div class="card empty"><p>Loading...</p></div>
    <div class="card empty"><p>Loading...</p></div>
  `;

  loadTherapists().then(() => {
    startCarousel(group); // start pixel-based scroll
  });

  // Load reviews on page load
  loadReviews();
});

async function loadTherapists() {
  try {
    const res = await fetch('../assets/php/get_therapists.php');
    const data = await res.json();
    const group = document.getElementById('therapistGroup');
    group.innerHTML = '';

    const therapists = data.success ? data.therapists : [];
    const totalCards = 12;

    for (let i = 0; i < totalCards; i++) {
      const t = therapists[i];
      const img = t && t.PHOTO_PATH ? '../' + t.PHOTO_PATH : '../assets/images/icon.jpg';
      const name = t ? `Dr. ${escapeHtml(t.name)}` : 'No therapist';

      group.innerHTML += `
        <div class="card">
          <img src="${img}" onerror="this.src='../assets/images/icon.jpg'">
          <p>${name}</p>
        </div>`;
    }

    // duplicate cards for infinite scroll
    group.innerHTML += group.innerHTML;

  } catch (e) {
    console.error(e);
  }
}

function startCarousel(group) {
  let scrollPos = 0;
  const SPEED = 100;
  
  function animate() {
    scrollPos += SPEED / 100;
    
    if (scrollPos >= group.scrollWidth / 2) {
      scrollPos = 0;
    }
    
    group.style.transform = `translateX(-${scrollPos}px)`;
    requestAnimationFrame(animate);
  }

  requestAnimationFrame(animate);
}

function escapeHtml(str) {
  return str ? str.replace(/[&<>"']/g, m =>
    ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])
  ) : '';
}

/* =============================
   REVIEWS SLIDER - COMPLETELY REWRITTEN
============================= */
let currentReviewIndex = 0;

async function loadReviews() {
  try {
    const res = await fetch('../assets/php/get_reviews.php');
    const data = await res.json();

    const slider = document.getElementById('reviewsSlider');
    const dotsContainer = document.getElementById('reviewsDots');
    
    slider.innerHTML = '';
    dotsContainer.innerHTML = '';

    if (!data.success || data.reviews.length === 0) {
      slider.innerHTML = `
        <div class="review-slide active">
          <div class="review-content">
            <h2>"Be the first to leave a review"</h2>
          </div>
        </div>`;
      
      dotsContainer.innerHTML = '<span class="dot active"></span>';
      return;
    }

    // Create slides
    data.reviews.forEach((review, index) => {
      const slide = document.createElement('div');
      slide.className = 'review-slide' + (index === 0 ? ' active' : '');
      slide.innerHTML = `
        <div class="review-content">
          <h2>"${escapeHtml(review.comment)}"</h2>
          <p>- ${escapeHtml(review.user_name)}</p>
        </div>`;
      slider.appendChild(slide);
    });

    // Create dots
    data.reviews.forEach((_, index) => {
      const dot = document.createElement('span');
      dot.className = 'dot' + (index === 0 ? ' active' : '');
      dot.addEventListener('click', () => goToReview(index));
      dotsContainer.appendChild(dot);
    });

    currentReviewIndex = 0;

  } catch (e) {
    console.error('Error loading reviews:', e);
    const slider = document.getElementById('reviewsSlider');
    const dotsContainer = document.getElementById('reviewsDots');
    
    slider.innerHTML = `
      <div class="review-slide active">
        <div class="review-content">
          <h2>"Reviews coming soon"</h2>
        </div>
      </div>`;
    
    dotsContainer.innerHTML = '<span class="dot active"></span>';
  }
}

function goToReview(index) {
  const slides = document.querySelectorAll('.review-slide');
  const dots = document.querySelectorAll('.dot');
  
  // Remove active class from all
  slides.forEach(slide => slide.classList.remove('active'));
  dots.forEach(dot => dot.classList.remove('active'));
  
  // Add active class to current
  slides[index].classList.add('active');
  dots[index].classList.add('active');
  
  currentReviewIndex = index;
}


function preventBackNavigation() {
    window.history.pushState(null, document.title, window.location.href);
            window.addEventListener('popstate', function (event) {
        window.location.href = "loginPage.php";
    });
}

preventBackNavigation();

window.onload = function() {
    preventBackNavigation();
};


</script>

</body>
</html>