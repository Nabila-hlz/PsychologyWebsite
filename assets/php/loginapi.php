<?php
require_once 'dbconnection.php';
session_start();
//1 get inputs
//2 validate them empty + structure as stored + Sanitize (Protect against:SQL Injection,XSS)
//3 Connect to the database
//Authentication:
//4 Check if the user exists (queries)
//5 Verify the password : hashed , password_verify() , error msg
//6 if valid Start session or  generate token (User would need to log in for every page , Password would be sent repeatedly (dangerous)
//Authorization:
//7 Save role in session or token after login
//8 Redirect user based on role (switch ($_SESSION['role']) case statments / header("Location:");+  break;)
//security: Protect each dashboard
//9 Each dashboard must check: Is the user logged in? Does the role match?
//add forgot password if possible at the end


//about rememebr me : => use session / cookie / token (saved in db + in cookie)
//“Keep the user logged in even after the browser is closed”
//This is done using a persistent cookie + secure token.
//1 rememder db table 
//2 start session after login
//3 generate the token + expire date 
//4 store it in the db and cookie


//the difference btw pages when logged in and when logged out : how to manage that ?


// --------------------- login : -----------------------------
/* ---------------------------------------------------------------------------------------------------------------------------------
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0); // don’t show them on page
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

function respond($success, $message = "", $redirect = "")
{
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "redirect" => $redirect
    ]);
    exit;
}*/

/*
header("Content-Type: application/json");

function error($msg)
{
    echo json_encode([
        "success" => false,
        "message" => $msg
    ]);
    exit;
}
*/
//------------------------------------------------------------------------------------------------------------------------------------
// helper function to store errors
function addError($msg)
{
    if (!isset($_SESSION['errors'])) {
        $_SESSION['errors'] = [];
    }
    $_SESSION['errors'][] = $msg;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize form data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    //required inputs
    if (empty($email) || empty($password)) {
        addError("Please fill in all fields.");
        header("Location: ../src/loginPage.php"); // redirect back to login
        exit;
    }
    //validate the email:
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        addError("Invalid email format.");
        header("Location: ../src/loginPage.php");
        exit;
    }




    // Fetch user
    /*
    $stmt = $conn->prepare("SELECT USER_ID, PASSWORD_HASH, ROLE FROM `USER` WHERE EMAIL = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($passhash, $user['PASSWORD_HASH'])) {
        die("Invalid credentials");
    }
    */

    $stmt = $conn->prepare("SELECT * FROM `USER` WHERE EMAIL = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        addError("User not found.");
        header("Location: ../../src/loginPage.php");
        exit;
    }
    $storedHash = trim($user['PASSWORD_HASH']);
    if (!password_verify($password, $storedHash)) {
        addError("Incorrect password.");
        header("Location: ../../src/loginPage.php");
        exit;
    }

    /*
    if (!password_verify($passhash, $user['PASSWORD_HASH'])) {
        die("Error! Invalid password");
    }
    */

    //---------------------Create session

    $_SESSION['user_id'] = $user['USER_ID'];
    $_SESSION['role'] = $user['ROLE'];


    //----------------------- Remember me
    if (isset($_POST['remember_me'])) {
        // Before inserting new token delete old ones of that user if there exist 
        $conn->query("DELETE FROM REMEMBER_TOKENS WHERE USER_ID = {$user['USER_ID']} OR EXPIRE_DATE < NOW()");

        $token = bin2hex(random_bytes(32)); //create random token to save it in the cookie
        $tokenHash = hash('sha256', $token); //hash it to save it in the db
        $expires = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); //30 days from now

        //STORE THE TOKEN IN DB
        $stm = $conn->prepare("INSERT INTO REMEMBER_TOKENS (USER_ID, TOKEN_HASH, EXPIRE_DATE) VALUES (?, ?, ?)");
        $stm->bind_param('iss', $user['USER_ID'], $tokenHash, $expires);
        $stm->execute();

        //store the token in cookie:
        setcookie(
            'remember_me',           // 1. Cookie name
            $token,                  // 2. Cookie value
            time() + 60 * 60 * 24 * 30,    // 3. Expiration time
            '/',                      // 4. Path?
            '',                       // 5. Domain?
            true,                     // 6. Secure
            true                      // 7. HttpOnly?
        );
    }
    /*
    // -----------------------Redirect by role
    // Return JSON with redirect URL
    $redirect = match ($user['ROLE']) {
        'admin' => '../../src/admin.html',
        'therapist' => '../../src/psycologist.html',
        'patient' => '../../src/user_dashboard.html',
        default => '../../src/loginPage.php'
    };
    header("Location: $redirect");
    exit;
    */

    //redirecting the user to its dashboard
    

    switch ($user['ROLE']) {
        case 'admin':
            header("Location: http://localhost/InnerBloom/assets/php/admin_dashboard.php");
            break;
        case 'therapist':
            header("Location: http://localhost/InnerBloom/src/psychologist.html");
            break;
        case 'patient':
            header("Location: http://localhost/InnerBloom/src/user_dashboard.html");
            break;
        default:
            header("Location: http://localhost/InnerBloom/src/loginPage.php");
            exit;
    }
    exit;
}
$conn->close();
?>