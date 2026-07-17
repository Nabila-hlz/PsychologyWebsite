<?php
session_start();
if (isset($_SESSION['error'])) {
    echo "<script>alert('{$_SESSION['error']['msg']}');</script>";
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sign Up | InnerBloom</title>
    <link rel="icon" type="x-icon" href="../assets/images/logoNo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Radley:ital@1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/signup.css">
</head>

<body>

    <div class="backgroung">
        <div class="wrapper">

            <div class="form">
                <form id="signupForm" method="post" action="../assets/php/signup.php">

                    
                    <div class="name input-group">
                        <input type="text" name="firstname" id="firstname" placeholder="First name" required />
                        <input type="text" name="lastname" id="lastname" placeholder="Last name" required />
                    </div>
                    <div class="input-group">
                        <input type="text" name="username" id="username" placeholder="Username" required />
                    </div>
                    <div class="input-group">
                        <input type="email" name="email" id="email" placeholder="Enter your email" required />
                    </div>
                    <div class="input-group password">
                        <input type="password" name="password" id="password" placeholder="Enter your password"
                            required />
                        <i class="fas fa-eye-slash" id="togglePassword1" class="toggle-icon"></i>
                    </div>

                    <div class="input-group password">
                        <input type="password" name="confirm-password" id="confirm-password"
                            placeholder="Confirm your password" required />
                        <i class="fas fa-eye-slash" id="togglePassword2" class="toggle-icon"></i>
                    </div>

                    <div class="input-group">
                        <input type="tel" name="phone" id="phone" placeholder="phone number">
                    </div>
                    <div class="input-group">
                        <select name="gender" id="gender" required>
                            <option value="" disabled selected>Select your gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="terms-conds">
                        <input type="checkbox" name="terms-conds" id="terms-conds" required>
                        <label for="terms-conds">I agree to the <a href="termsandconds.html">Terms and
                                Conditions</a>.</label>
                    </div>
                    <button type="submit" class="signUp">Sign Up</button>
                </form>
            </div>

            <div class="swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="../assets/images/slide1.jpg" alt="Slide 1"></div>
                    <div class="swiper-slide"><img src="../assets/images/slide2.jpg" alt="Slide 2"></div>
                    <div class="swiper-slide img3"><img src="../assets/images/slide3.png" alt="Slide 3"></div>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

</body>
<!-- include Swiper JS at the end -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    let swiperInstance = null;
    const swiperContainer = document.querySelector('.swiper');

    function initSwiper() {
        if (window.innerWidth >= 700 && !swiperInstance) {
            // Make swiper visible before initializing
            swiperContainer.style.display = 'block';
            swiperInstance = new Swiper('.swiper', {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                speed: 600,
                effect: 'slide',
                on: {
                    init: function() {
                        // Ensure swiper is properly displayed after initialization
                        swiperContainer.style.display = 'block';
                    }
                }
            });
        }
    }

    function handleResize() {
        if (window.innerWidth < 700) {
            // Destroy swiper and hide container on small screens
            if (swiperInstance) {
                swiperInstance.destroy(true, true);
                swiperInstance = null;
            }
            swiperContainer.style.display = 'none';
        } else {
            // On larger screens, show container and initialize swiper
            swiperContainer.style.display = 'block';
            initSwiper();
        }
    }

    // Initial load
    handleResize();

    // On resize
    window.addEventListener('resize', handleResize);

    // the toggle of eye icon
    const togglePassword2 = document.getElementById('togglePassword2');
    const confirmpassword = document.getElementById('confirm-password');
    const togglePassword1 = document.getElementById('togglePassword1');
    const password = document.getElementById('password');

    togglePassword1.addEventListener('click', () => {
        // Toggle password visibility
        if (password.type === 'password') {
            password.type = 'text';
            togglePassword1.classList.remove('fa-eye-slash');
            togglePassword1.classList.add('fa-eye');
        } else {
            password.type = 'password';
            togglePassword1.classList.remove('fa-eye');
            togglePassword1.classList.add('fa-eye-slash');
        }
    });

    togglePassword2.addEventListener('click', () => {
        // Toggle password visibility
        if (confirmpassword.type === 'password') {
            confirmpassword.type = 'text';
            togglePassword2.classList.remove('fa-eye-slash');
            togglePassword2.classList.add('fa-eye');
        } else {
            confirmpassword.type = 'password';
            togglePassword2.classList.remove('fa-eye');
            togglePassword2.classList.add('fa-eye-slash');
        }
    });

    // ---------------- Validation ----------------
    function isPhoneValid(phone) {
        const cleaned = phone.replace(/[\s-]/g, "");
        return phone === "" || /^(05|06|07)[0-9]{8}$/.test(cleaned);
    }

    function checkInputs() {
        const firstName = document.getElementById("firstname").value.trim();
        const lastName = document.getElementById("lastname").value.trim();
        const username = document.getElementById("username").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();
        const confirmPassword = document.getElementById("confirm-password").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const terms = document.getElementById("terms-conds").checked;
        const gender = document.getElementById('gender').value;

        //patterns
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const namePattern = /^[A-Za-z\s]+$/;
        const passwordPattern = /[^\s]/;

        //all required attributes
        if (!firstName || !lastName || !gender || !username || !email || !password || !confirmPassword) {
            alert("Please fill all required fields.");
            return false;
        }
        if (!namePattern.test(firstName)) {
            alert("Please enter a valid first name.");
            return false;
        }
        if (!namePattern.test(lastName)) {
            alert("Please enter a valid last name.");
            return false;
        }
        if (!emailPattern.test(email)) {
            alert("Please enter a valid email.");
            return false;
        }
        if (password.length < 6) {
            alert("Password must be at least 6 characters.");
            return false;
        }
        if (!passwordPattern.test(password)) {
            alert("Password must contain at least one character, number, or symbol!");
            return false;
        }
        if (password !== confirmPassword) {
            alert("Password does not match the confirm password");
            return false;
        }
        if (!isPhoneValid(phone)) {
            alert("Please enter a valid phone number.");
            return false;
        }
        if (!terms) {
            alert("You must agree to the terms.");
            return false;
        }

        return true;
    }

    document.getElementById("signupForm").addEventListener("submit", function(event) {
        event.preventDefault();
        if (!checkInputs()) return;
        this.submit();
    });
</script>

</html>
