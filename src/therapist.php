<?php
session_start();
if (isset($_SESSION['error'])) {
    echo "<script>alert('{$_SESSION['error']['msg']}');</script>";
    unset($_SESSION['error']);
} 
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/therapist.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="x-icon" href="../assets/images/logoNo.png">
    <title>Therapist Application | InnerBloom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Radley:ital@1&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <img src="../assets/images/logo.png" alt="InnerBloom logo">
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="therapist.php">Therapist</a>
                <a href="about.html">About us</a>
                <a href="../assets/php/login.php">Log In</a>
            </div>
        </nav>
    </header>


    <main>
        <section class="hero">
            <div class="slogan">
                <h1>Guiding your soul<br>Guarding your body
                <br>
                <br>
                <button class="apply-btn">Apply Now</button></h1>
            </div>
            <div class="therapist-img">
                <img src="../assets/images/chairs.png" alt="therapist job">
            </div>
        </section>
        <section class="motivation">
            <div class="motivation-msg">
                <h2>Why Join InnerBloom?</h2>
                <p>We believe that therapy should be accessible, ethical, and empathetic. Our platform connects
                    qualified professionals with individuals seeking balance, healing, and growth. As part of
                    InnerBloom, you’ll help people bloom from within.</p>
            </div>
            <div class="benefits">
                <div class="benefit-box">
                    <i class="fa-solid fa-clock"></i>
                    <h3>Flexibility</h3>
                    <p>Set your own schedule and work from anywhere. You decide when and how many clients to see.
                    </p>
                </div>
                <div class="benefit-box">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <h3>Additional Income</h3>
                    <p>Earn extra revenue while doing what you love — helping clients grow and heal.</p>
                </div>
                <div class="benefit-box">
                    <i class="fa-solid fa-bell"></i>
                    <h3>Easy Notification System</h3>
                    <p>Stay on top of client requests with dashboard alerts and email notifications for upcoming
                        sessions or messages.</p>
                </div>
                <div class="benefit-box">
                    <i class="fa-solid fa-chart-line"></i>
                    <h3>Professional Growth</h3>
                    <p>Create and share articles, videos, and resources in our exclusive community center. Build your
                        professional portfolio, engage with clients, and Receive insights, feedback, and analytics to
                        improve your practice.</p>
                </div>
            </div>
        </section>
        <section class="requirements">
            <h2>Requirements & Qualifications</h2>
            <ul>
                <li>Valid psychology or therapy certification</li>
                <li>Minimum 1 year of counseling or therapy experience</li>
                <li>Commitment to confidentiality and ethical standards</li>
                <li>Profile Essentials: Professional photo, Short biography/motivation, CV upload</li>
            </ul>
        </section>
        <div class="apply-now">
            <h2>Ready to join us?</h2>
            <p>We’re excited to meet you! Fill out the form below to become part of our growing network.</p>
            <button id="apply-btn">Apply Now</button>
        </div>
    </main>



    <!-- MODAL FOR THERAPIST FORM -->

    <div id="therapistModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <br>
            <div class="wrapper">
                <form id="modalform" action="../assets/php/therapistform.php"
                    method="POST" enctype="multipart/form-data">

                    <div class="name">
                        <label for="name">Name
                            <span class="remark">(as on your certificate)</span>
                        </label>
                        <div class="name-row">
                            <input type="text" name="firstname" id="firstname" placeholder="First name" required>
                            <input type="text" name="lastname" id="lastname" placeholder="Last name" required>
                        </div>
                    </div>

                    <div class="email">
                        <label for="email">Email
                            <span class="remark">(required)</span>
                        </label>
                        <input type="email" name="email" id="email" placeholder="enter your professional email"
                            required>
                    </div>

                    <div class="password">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" required>
                            <i class="fas fa-eye-slash" id="togglePassword"
                                style="cursor: pointer; margin-left: -30px;"></i>
                        </div>
                    </div>

                    <div class="number">
                        <label for="number">Contact Number<span class="remark">(optional)</span></label>
                        <input type="tel" name="number" id="number" placeholder="+213">
                    </div>

                    <div class="gender">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender" required>
                            <option value="" disabled selected>Select your gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="specialty">
                        <label for="specialty">specialty</label>
                        <select id="specialty" name="specialty" required>
                            <option value="" disabled selected>Select your specialty</option>
                            <option value="Cognitive Behavioral Therapy">Cognitive Behavioral Therapy </option>
                            <option value="Family & Couples Therapy">Family & Couples Therapy</option>
                            <option value="Child & Adolescent Psychology">Child & Adolescent Psychology</option>
                            <option value="Clinical Psychology">Clinical Psychology</option>
                            <option value="Mindfulness & Stress Management">Mindfulness & Stress Management</option>
                            <option value="Educational Psychology">Educational Psychology</option>
                        </select>
                    </div>

                    <div class="session-price">
                        <label for="price">Price per session
                            <span class="remark">(optional)</span>
                        </label>
                        <input type="text" name="price" id="price" placeholder="DA">
                        <p class="remark">Please consider our <strong>20% service fee</strong> when setting your rate.
                        </p>
                    </div>

                    <div class="availability">
                        <label for="availability">Weekly availability</label>
                        <div class="days">
                            <label><input type="checkbox" name="availability[]" value="Sunday"> Sunday</label>
                            <label><input type="checkbox" name="availability[]" value="Monday"> Monday</label>
                            <label><input type="checkbox" name="availability[]" value="Tuesday"> Tuesday</label>
                            <label><input type="checkbox" name="availability[]" value="Wednesday"> Wednesday</label>
                            <label><input type="checkbox" name="availability[]" value="Thursday"> Thursday</label>
                            <label><input type="checkbox" name="availability[]" value="Friday"> Friday</label>
                            <label><input type="checkbox" name="availability[]" value="Saturday"> Saturday</label>
                        </div>
                    </div>

                    <div class="payment-info">
                        <label for="payment-info">Payment References</label>
                        <input type="text" name="payment-info" id="payment-info" required>
                    </div>

                    <div class="bio">
                        <label for="bio">Short biography <span class="remark">(bio or motivation)</span></label>
                        <textarea name="bio" id="bio"
                            placeholder="Tell us about your background and experience..."></textarea>
                    </div>

                    <div class="certificate">
                        <label for="certificate">Certificates & Accreditations</label>
                        <div class="drop-area">
                            <button type="button" id="cert-browseBtn">Browse Files</button>
                            <input type="file" id="certificate" name="certificate"
                                accept=".pdf, .doc, .docx, .jpg, .png" hidden>
                        </div>
                    </div>

                    <div class="cv">
                        <label for="cv">CV (Curriculum Vitae) <span class="remark">(optional)</span></label>
                        <div class="drop-area">
                            <button type="button" id="cv-browseBtn">Browse Files</button>
                            <input type="file" name="cv" id="cv" accept=".pdf, .doc, .docx, .jpg, .png" hidden>
                        </div>
                        <span class="remark">Please upload your updated CV in PDF or Word format.</span>
                    </div>

                    <div class="photo">
                        <label for="photo">Profile photo <span class="remark">(optional)</span></label>
                        <div class="upload-section">
                            <label for="photo" class="upload-box">
                                <span>click to upload</span>
                                <input type="file" id="photo" name="photo" accept=".jpg, .png, .webp, .jpeg" hidden>
                            </label>
                        </div>
                    </div>

                    <div class="accuracy-checkbox">
                        <input type="checkbox" id="accuracy-checkbox" required>
                        <label>I confirm that all information is true and accurate.</label>
                    </div>

                    <div class="terms-conds">
                        <input type="checkbox" id="terms-conds" required>
                        <label>I agree to the <a href="termsandconds.html" target="_blank">Terms &
                                Conditions</a></label>
                    </div>

                    <button type="submit" class="submit">Submit</button>
                </form>

            </div>
        </div>
    </div>

    <script>
        // ------------------the toggle of eye icon
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            // Toggle password visibility
            if (password.type === 'password') {
                password.type = 'text'; // show password
                togglePassword.classList.remove('fa-eye-slash');
                togglePassword.classList.add('fa-eye'); // show normal eye icon when hidden
            } else {
                password.type = 'password'; // hide password
                togglePassword.classList.remove('fa-eye');
                togglePassword.classList.add('fa-eye-slash'); // show slash icon when visible
            }
        });


        // ---------------- Modal Functionality ----------------
        const modal = document.getElementById("therapistModal");
        const openBtns = document.querySelectorAll(".apply-btn, #apply-btn");
        const closeBtn = document.querySelector(".close");
        const cvBtn = document.getElementById("cv-browseBtn");
        const cvInput = document.getElementById("cv");
        const certBtn = document.getElementById("cert-browseBtn");
        const certInput = document.getElementById("certificate");
        const namePattern = /^[A-Za-z\s]+$/; //contains only letters or spaces

        openBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                modal.style.display = "block";
                document.body.style.overflow = "hidden";
            });
        });

        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        });

        window.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.style.display = "none";
                document.body.style.overflow = "auto";
            }
        });

        cvBtn.addEventListener("click", () => cvInput.click());
        certBtn.addEventListener("click", () => certInput.click());

        // ---------------- Validation Functions ----------------

        //helper functions:
        function isPhoneValid(phone) {
            if (phone === "") {
                return true; //can be null (not required)
            }
            const cleaned = phone.replace(/[\s-]/g, "");
            return /^(05|06|07)[0-9]{8}$/.test(cleaned);
        }

        function isPriceValid(price) {
            return price === "" || /^[0-9]+(\.[0-9]{1,2})?$/.test(price);
        }

        //validation function:
        function checkInputs() {
            //only required inputs and there formats : 
            const firstName = document.getElementById("firstname").value.trim();
            const lastName = document.getElementById("lastname").value.trim();
            //username can be null
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();
            const phone = document.getElementById("number").value.trim();
            const price = document.getElementById("price").value.trim();
            const availability = [...document.querySelectorAll('input[name="availability[]"]:checked')].map(cb => cb.value);
            const specialty = document.getElementById("specialty").value;
            const gender = document.getElementById("gender").value;
            const paymentref = document.getElementById("payment-info").value;
            const terms = document.getElementById("terms-conds").checked;
            const accuracy = document.getElementById("accuracy-checkbox").checked;
            //only the certificate is required
            const certificate = document.getElementById("certificate").files[0];
            //patterns
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; //contains @ and . (but no space and no @ before or after)
            const namePattern = /^[A-Za-z\s]+$/; //contains only letters or spaces 
            const passwordPattern = /[^\s]/; // at least one non-space character (easy check)

            if (!firstName || !lastName || !namePattern.test(lastName) || !namePattern.test(firstName)) {
                alert("Please enter your valid name (contains only letters or spaces).");
                return false;
            }
            if (!email || !emailPattern.test(email)) {
                alert("Please enter a valid email.");
                return false;
            }
            if (!password || password.length < 6) {
                alert("Password must be at least 6 characters.");
                return false;
            }
            if (!passwordPattern.test(password)) {
                alert("Password must contain at least one character, number, or symbol! (min 6 chars)");
                return false;
            }
            if (!isPhoneValid(phone)) {
                alert("Please enter a valid phone number.");
                return false;
            }
            if (!paymentref) {
                alert("Please enter your payment reference.");
                return false;
            }
            if (!isPriceValid(price)) {
                alert("Please enter a valid price in DZ (numbers only).");
                return false;
            }
            if (!specialty) {
                alert("Please select a specialty among the provided choices");
                return false;
            }
            if (!gender) {
                alert("Please select your gender");
                return false;
            }
            if (availability.length === 0) {
                alert("Please select at least one day");
                return false;
            }
            if (!certificate) {
                alert("Please upload your certificate.");
                return false;
            }
            if (!terms) {
                alert("You must agree to the terms.");
                return false;
            }
            if (!accuracy) {
                alert("You must confirm that all information is true and accurate.");
                return false;
            }

            return true;
        }

        // ---------------- Form Submission ----------------


        //event handler of the form submission
        document.querySelector("#modalform form").addEventListener("submit", function(event) {
            event.preventDefault();
            if (!checkInputs()) return;
            this.submit();
        });
    </script>

    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="../assets/images/footerpic.png" alt="InnerBloom Logo" height="70">
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
</body>

</html>