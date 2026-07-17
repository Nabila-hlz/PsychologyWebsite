<?php
session_start();
// Show errors if they exist in the previous attempt of login
if (isset($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $error) {
        echo "<script>alert('" . addslashes($error) . "');</script>";
    }
    unset($_SESSION['errors']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="x-icon" href="../assets/images/logoNo.png">
    <title>Login | InnerBloom</title>
    <link rel="stylesheet" href="..\assets\css\login.css" />
    <meta charset="UTF-8" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="googlebot" content="noindex, nofollow" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 ">
</head>

<body>
    <div>
        <div class="logo">
            <img src="../assets/images/logo.png" alt="InnerBloom logo" />
        </div>

        <div class="form">
            <form method="post" id="form" action="../assets/php/loginapi.php">
                <h1>Login</h1>
                <div class="email">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="enter your email" required />
                </div>
                <div class="password">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" placeholder="enter your password"
                            required />
                        <i class="fas fa-eye-slash" id="togglePassword"
                            style="cursor: pointer; margin-left: -30px;"></i>
                    </div>

                </div>
                <div class="remember-forgot">
                    <label for="remember_me">
                        <input type="checkbox" name="remember_me" id="remember_me" />
                        Remember me
                    </label>
                </div>
                <button type="submit" class="login">Login</button>
                <div class="register">
                    <p>
                        Don't have an account?
                        <a href="signupPage.php">sign up</a>
                    </p>
                </div>
            </form>



        </div>
    </div>

    <script>
        //--------------------- Toggle password visibility
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
        document.getElementById("form").addEventListener("submit", function(e) {
            e.preventDefault();
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();
            const remember = document.getElementById("remember_me").checked;

            if (!email || !password) {

                alert("Please enter both email and password.");
                return false;
            }
            this.submit();

        });
        
    </script>


</body>

</html>