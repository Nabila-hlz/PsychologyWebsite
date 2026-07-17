<?php

require_once 'dbconnection.php'; // include database connection
session_start();


try {
    //CHECK REQUEST METHOD
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Get and sanitize form data
        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $username = trim($_POST['username'] ?? ''); //to prevent problems when the input doesn't exist
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $phone = trim($_POST['phone'] ?? '');
        $gender = $_POST['gender'];
        $role = 'patient';

        //required inputs
        if (empty($email) || empty($password) || empty($firstname) || empty($lastname) || empty($gender)) {
            throw new Exception("Please fill in all required fields.");
        }
        //validate the email:
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        //validate the length of the password:
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters.");
        }
        //check if the email already exist in the db:
        $check = $conn->prepare("SELECT 1 FROM `user` WHERE EMAIL = ? LIMIT 1");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->fetch()) //if it returns false so there is no email in the db but if there is already one it returns an array so the entred email is not unique
        {
            throw new Exception("Email already registered.");
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        //empty inputs (for optional inputs only must be stored as null)
        if ($username === '') {
            $username = null; // store NULL in DB
        } else //check if the username is unique in the db 
        {
            $check = $conn->prepare("SELECT 1 FROM `user` WHERE USERNAME = ? LIMIT 1");
            $check->bind_param('s', $username);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                throw new Exception("Username already registered.");
            }
        }
        if ($phone === '') {
            $phone = null; // store NULL in DB
        } else //check the pattern of the phone
        {
            $pattern = '/^(05|06|07)\d{8}$/';
            if (!preg_match($pattern, $phone)) {
                throw new Exception("Invalid phone number.");
            }
        }
        //validate the name (only letters)
        if (!preg_match('/^[a-zA-Z\s]+$/', $firstname)) {
            throw new Exception("Invalid first name.");
        }
        if (!preg_match('/^[a-zA-Z\s]+$/', $lastname)) {
            throw new Exception("Invalid last name.");
        }

        //-------------------------insertion 

        $sql = "INSERT INTO `user` (FIRST_NAME, LAST_NAME, USERNAME, EMAIL, PASSWORD_HASH, ROLE, PHONE_NUMBER, GENDER)
        VALUES (?, ?, ?, ?, ?, ? , ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssss', $firstname, $lastname, $username, $email, $hashed_password, $role, $phone, $gender);
        $stmt->execute();


        $_SESSION['success'] = [
            'status' => true,
            'msg' => "user profile created successfully! login now to start your journey with innerbloom"
        ];
        //redirect the user to the login page
        header("Location: ../../src/loginPage.php");
        exit;
    }
} catch (Exception $e) {
    if (!isset($_SESSION['error'])) {
        $_SESSION['error'] = [];
    }
    $_SESSION['error'] = [
        'status' => true,
        'msg' => $e->getMessage()
    ];
    header("Location: ../../src/signupPage.php");
    exit;
}
$conn->close();
