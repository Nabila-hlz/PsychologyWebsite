<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "dbconnection.php";
  
if ($_SERVER["REQUEST_METHOD"]==="POST"){
    $first_name= htmlspecialchars($_POST['first_name']);
    $last_name= htmlspecialchars($_POST['last_name']);
    $email= htmlspecialchars($_POST['email']);
    $message= htmlspecialchars($_POST['message']);

    if(empty($first_name) || empty($last_name) || empty($email) || empty($message)) {
        echo "All fields are required";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        exit;
    }
    $stmt= $conn->prepare("INSERT INTO contact_us (email, first_name, last_name, message ) VALUES( ? , ? , ? , ?) ");
    if ($stmt === false) {
        echo "Prepare failed: " . $conn->error;
        exit;
    }
    
    $stmt->bind_param("ssss", $email, $first_name, $last_name, $message);
    if ( $stmt->execute() ){
    
        echo '
    <div class="box">
        <h2>✅ Message Sent </h2>
        <p>We will contact you soon.</p>
        <!--<a href="http://localhost/innerbloom/src/about.html">Go back</a>-->
    </div>'
;

    }
    else{
        echo " Error: ".$stmt->error;
    }
    $stmt->close();
}
$conn->close();

?>