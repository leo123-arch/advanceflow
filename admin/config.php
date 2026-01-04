<?php

$host = "localhost";   // MySQL server
$user = "root";        // default username in XAMPP
$pass = "";            // default password is empty
$db   = "career_system"; // your database name

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

// echo "Connected successfully"; // Use for testing only

?>
