<?php
session_start();
include 'db.php'; // Ensure this file exists and connects properly

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // --- SECURITY FIX: Prepared Statements ---
    // We use ? placeholders instead of putting variables directly in the query
    $sql = "SELECT * FROM users WHERE (email = ? OR student_id = ?) AND role = ? LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    // "sss" means String, String, String (for email, student_id, role)
    $stmt->bind_param("sss", $email, $email, $role); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // --- SESSION SETUP ---
            // Save the data you want to display on the dashboard here
            $_SESSION['user_id'] = $row['userID'];
            $_SESSION['role'] = $row['role'];       // e.g., "Student"
            $_SESSION['full_name'] = $row['full_name']; // e.g., "Juan Cruz"
            
            // This solves your previous question! 
            // Make sure your database column is actually named 'username'
            $_SESSION['username'] = $row['username']; 
            
            // Redirect based on the role stored in the DATABASE (not the form)
            // We use strtolower to make it case-insensitive just in case
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
        echo "<script>alert('User not found or role mismatch.'); window.history.back();</script>";
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

            <div>
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="Student">Student</option>
                    <option value="Teacher">Teacher</option>
                    <option value="Librarian">Librarian</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>

            <button type="submit" class="signin-btn">Sign In</button>
            <button type="button" class="forgot-btn" onclick="window.location.href='forgot_password.html'">Forgot password?</button>
        </form>

        <div class="footer">
            <p>Don't have an account?</p>
            <button onclick="window.location.href='register.php'">Create new account</button>
        </div>
    </div>

</body>
</html>