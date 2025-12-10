<?php
session_start();
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    // REMOVED: $role = $_POST['role']; we don't need this anymore

    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // --- MODIFIED QUERY ---
    // We removed "AND role = ?"
    // Now we just look for the user based on email/ID. 
    $sql = "SELECT * FROM users WHERE email = ? OR student_id = ? LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    
    // --- MODIFIED BINDING ---
    // Changed "sss" to "ss" because we only have 2 variables now (email, email)
    $stmt->bind_param("ss", $email, $email); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // --- SESSION SETUP ---
            $_SESSION['user_id'] = $row['userID'];
            
            // The database now decides the role automatically
            $_SESSION['role'] = $row['role'];       
            $_SESSION['full_name'] = $row['full_name']; 
            
            // Note: Double check if your column is 'fullName' or 'full_name' in your DB
            $_SESSION['username'] = $row['fullName']; 
            
            // --- AUTOMATIC REDIRECT ---
            // This logic works perfectly without changes. 
            // It reads the role from the database ($row['role']) and sends them to the right place.
            $db_role = strtolower($row['role']);

            if ($row['role'] == "Librarian") {
                header("Location: librarian_dashboard.php");
            } elseif ($row['role'] == "Teacher") {
                header("Location: teacher_dashboard.php");
            } elseif ($row['role'] == "Student") {
                header("Location: student_dashboard.php");
            } else {
                header("Location: staff_dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid password.'); window.history.back();</script>";
        }
    } else {
        // Changed error message since "role mismatch" is no longer possible
        echo "<script>alert('User not found.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Login</title>
    <link rel="stylesheet" href="loginstyle.css">
</head>
<body>
    
    <div class="container">
        <div class="header">
            <div class="emoji">📚</div>
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
        </div>

        <form action="login.php" method="POST">
            <div class="input-field">
                <label for="email">Email/Username</label>
                <input type="text" id="email" name="email" placeholder="Enter your email or username" required>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="signin-btn">Sign In</button>
            <button type="button" class="forgot-btn" onclick="window.location.href='forgot_password.html'">Forgot password?</button>
        </form>

        <div class="footer">
            <p>Don't have an account?</p>
            <button onclick="window.location.href='register.php'">Create new account</button>
        </div>
    </div>
    <div class="title">
        <a href="landing_page.php">📚 LIBRARY</a>
    </div>

</body>
</html>