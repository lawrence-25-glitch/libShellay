<?php
    $host = "localhost";
    $user = "root";
    $pass = ""; 
    $db = "library"; // Ensure this database exists

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>