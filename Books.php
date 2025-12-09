<?php
    include('db.php');

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(isset($_POST['title']) && isset($_POST['author']) && isset($_POST['year'])){
            $title = $_POST['title'];
            $author = $_POST['author'];
            $year = $_POST['year'];

            $stmt = $conn->prepare("INSERT INTO books (title, author, year) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $title, $author, $year);

            if($stmt->execute()){
                echo "New book added successfully.";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "All fields are required.";
        }
    }
?>