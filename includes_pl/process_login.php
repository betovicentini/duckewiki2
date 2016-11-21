<?php
include_once 'db_connect.php';
include_once 'functions.php';
 
sec_session_start(); // Our custom secure way of starting a PHP session.
 
if (isset($_POST['email'], $_POST['p'])) {
    $email = $_POST['email'];
    $pwd = $_POST['p']; // The hashed password.

	$resLogin = login($email, $pwd, $conn);
    if ($resLogin === 'ok') {
        // Login success 
        header("Location: $pathtoplantas2/main.php");
    } else {
        // Login failed
        header("Location: $pathtoplantas2/index.php?error=1&msg=$resLogin");
    }
} else {
    // The correct POST variables were not sent to this page. 
    echo 'Invalid Request';
}
?>
