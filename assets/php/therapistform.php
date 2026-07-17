<?php
//workflow:
//*use transaction
//*Validate files → size, type, extension
//*Use user_id for folder
//1.receive all inputs
//2.craete the user in the db
//3.get the user_id created
//4.create the path of the files with the user_id
//5.check the file's type
//6.move_uploaded_file($photoFile['tmp_name'], $photoPath)
//7.store the extra info and the paths by the user_id as fk in the therapist table 
//8.$pdo->commit(); if problem rollback + err msg


require_once 'dbconnection.php'; // include database connection

session_start();
$transaction_started = false;
try {
    //CHECK REQUEST METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }
    // Get and sanitize form data
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = $firstname . ' ' . $lastname;
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['number']);
    $gender = $_POST['gender'];
    $role = 'therapist';
    $session_price = trim($_POST['price']  ?? NULL);
    $payment_reference = trim($_POST['payment-info']);
    $bio = trim($_POST['bio']) ?? NULL;
    $specialty = $_POST['specialty'];
    $availability = $_POST['availability'] ?? [];

    //files:
    //initialization:
    $certificate_path = null; // cannot be empty
    $cv_path = null; //can be null
    $photo_path = null; //can be null
    //validating files:
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] !== UPLOAD_ERR_NO_FILE) {

        $certificate = $_FILES['certificate'];
        // 1️.Check for upload errors
        if ($certificate['error'] !== UPLOAD_ERR_OK) {
            throw new Exception(" Error uploading certificate");
        }

        // 2️.Validate file size (max 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if ($certificate['size'] > $maxSize) {
            throw new Exception("Certificate is too large! Max 2MB allowed");
        }

        // 3️.Validate file extension
        $allowedExt = ['pdf', 'doc', 'jpg', 'png'];
        $fileName = $certificate['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            throw new Exception("Invalid certificate filetype ");
        }
    } else {
        throw new Exception("Certificate is required");
    }

    if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
        $cv = $_FILES['cv'];
        // 1️.Check for upload errors
        if ($cv['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading cv");
        }

        // 2️.Validate file size (max 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if ($cv['size'] > $maxSize) {
            throw new Exception("cv is too large! Max 2MB allowed");
        }

        // 3️.Validate file extension
        $allowedExt = ['pdf', 'doc', 'docx', 'jpg', 'png'];
        $fileName = $cv['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            throw new Exception("Invalid cv filetype ");
        }
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $photo = $_FILES['photo'];
        // 1️.Check for upload errors
        if ($photo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading photo");
        }

        // 2️.Validate file size (max 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if ($photo['size'] > $maxSize) {
            throw new Exception("photo is too large! Max 2MB allowed");
        }

        // 3️.Validate file extension
        $allowedExt = ['jpg', 'png', 'webp', 'jpeg'];
        $fileName = $photo['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            throw new Exception("Invalid photo filetype");
        }
    }


    //required inputs
    if (empty($payment_reference) || empty($specialty) || empty($availability) || empty($email) || empty($password) || empty($firstname) || empty($lastname) || empty($gender)) {
        throw new Exception("Missing required fields");
    }

    //validate the email:
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email");
    }

    //validate the length of the password:
    if (strlen($password) < 6) {
        throw new Exception("invalid password! the password  must contain at least 6 characters");
    }

    //check if the email already exist in the db:
    $check = $conn->prepare("SELECT 1 FROM `user` WHERE EMAIL = ? LIMIT 1");
    $check->bind_param('s', $email);
    $check->execute();

    if ($check->fetch()) //if it returns false so there is no email in the db but if there is already one it returns an array so the entred email is not unique
    {
        throw new Exception("Email already registered");
    }
    $check->close();

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
        if ($check->fetch()) //if it returns false so there is no email in the db but if there is already one it returns an array so the entred email is not unique
        {
            throw new Exception("username already registered");
        }
        $check->close();
    }
    if ($phone === '') {
        $phone = null; // store NULL in DB
    } else //check the pattern of the phone
    {
        $pattern = '/^(05|06|07)\d{8}$/';
        if (!preg_match($pattern, $phone)) {
            throw new Exception("Invalid phone number");
        }
    }
    //validate the name (only letters)
    if (!preg_match('/^[a-zA-Z\s]+$/', $firstname) || !preg_match('/^[a-zA-Z\s]+$/', $lastname)) {
        throw new Exception("Invalid name");
    }

    //the transaction of inserting the therapist info in all tables:

    $conn->begin_transaction();
    $transaction_started = true;

    //1.insert the user attributes in the user table 
    $sql = "INSERT INTO `user` (FIRST_NAME, LAST_NAME, USERNAME, EMAIL, PASSWORD_HASH, ROLE, PHONE_NUMBER, GENDER)
        VALUES (?, ?, ?, ?, ?, ? , ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssss', $firstname, $lastname, $username, $email, $hashed_password, $role, $phone, $gender);

    if (!$stmt->execute()) {
        throw new Exception("Insert in user table failed");
    }

    // 2️.Getting the generated user ID
    $userId = $conn->insert_id;

    //3.uploading files in the folder and create their paths to be stord in the db :

    //certificate:
    $certExt = pathinfo($certificate['name'], PATHINFO_EXTENSION);
    //checking the existence of the folder:
    $dir = "../uploads/certificate/";
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $certificate_path = "../uploads/certificate/{$userId}certificate.$certExt";
    if (!move_uploaded_file($certificate['tmp_name'], $certificate_path)) {
        throw new Exception("Failed to move certificate file");
    }
    //cv:
    if (isset($cv)) {
        //upload the cv if it exist
        $cvExt = pathinfo($cv['name'], PATHINFO_EXTENSION);
        //checking the existence of the folder:
        $dir = "../uploads/cv/";
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $cv_path = "../uploads/cv/{$userId}cv.$cvExt";

        if (!move_uploaded_file($cv['tmp_name'], $cv_path)) {
            throw new Exception("Failed to move cv file");
        }
    } //otherwise the path is null

    //photo:
    if (isset($photo)) {
        //upload the photo i it exist
        $photoExt = pathinfo($photo['name'], PATHINFO_EXTENSION);
        //checking the existence of the folder:
        $dir = "../uploads/photo/";
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $photo_path = "../uploads/photo/{$userId}photo.$photoExt";

        if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
            throw new Exception("Failed to move photo file ");
        }
    } //otherwise the path is null

    //4. Inserting into therapist tb using userid as fk
    $query = "INSERT INTO `therapist` (THERAPIST_ID, SESSION_PRICE, PAYMENT_REF, BIO, CERTIFICATE_PATH, CV_PATH, PHOTO_PATH) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idsssss", $userId, $session_price, $payment_reference, $bio, $certificate_path, $cv_path, $photo_path);

    if (!$stmt->execute()) {
        throw new Exception("Inserting in therapist table failed");
    }

    //5. insertinf availibility and specialty in their tables

    //specialty :
    //get specialty id
    if ($specialty !== 'other') {
        $stmt = $conn->prepare("SELECT SPECIALTY_ID FROM `specialty` WHERE SPECIALTY_NAME = ?");
        $stmt->bind_param('s', $specialty);
        if (!$stmt->execute()) {
            throw new Exception("Failed to fetch specialty ID");
        }
        $stmt->bind_result($specialty_id);
        $stmt->fetch();
        $stmt->close();

        //insert in specialty_therapist 
        $stmt = $conn->prepare("INSERT INTO `specialty_therapist`(SPECIALTY_ID, THERAPIST_ID) VALUES(?, ?)");
        $stmt->bind_param('ii', $specialty_id, $userId);

        if (!$stmt->execute()) {
            throw new Exception("Inserting in specialty_therapist table failed");
        }
    }
    //availability days
    $stmt = $conn->prepare("INSERT INTO `availability` (THERAPIST_ID, DAY) VALUES (?, ?)");
    foreach ($availability as $day) {
        $stmt->bind_param("is", $userId, $day);

        if (!$stmt->execute()) {
            throw new Exception("Inserting in availability table failed");
        }
    }

    $conn->commit();
    
    $_SESSION['success'] = [
        'status' => true,
        'msg' => "Therapist profile created successfully! login now to start your journey with innerbloom"
    ];
    header("Location: ../../src/therapist.php");
    exit;
    
} catch (Exception $e) {

    if ($transaction_started) {
        $conn->rollback();
    }
    //to handle the problem of storing files when a problem occurs => delete them 
    $paths = [$certificate_path, $cv_path, $photo_path];
    foreach ($paths as $path) {
        if ($path && file_exists($path)) {
            unlink($path);
        }
    }
    $_SESSION['error'] = [
        'status' => true,
        'msg' => $e->getMessage()
    ];
    header("Location: ../../src/therapist.php");
    exit;
    
}
$conn->close();
