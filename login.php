<?php
session_start();
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check by email or student_id
    $sql = "SELECT * FROM users WHERE email = ? OR student_id = ? LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $email); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            
            // --- 1. CRITICAL CHECK: IS THE REGISTRATION APPROVED? ---
            // If the status is 'pending', we STOP them here.
            // If we remove this, they can log in without approval.
            if ($row['status'] === 'pending') {
                echo "<script>
                    alert('❌ Account Pending Approval!\\n\\nPlease wait for the Librarian to verify your registration.\\nVisit the library with your ID for faster approval.');
                    window.location.href = 'login.php';
                </script>";
                exit();
            }
            // ---------------------------------------------------------

            // --- 2. SUCCESS: SET SESSION ---
            $_SESSION['user_id'] = $row['userID'];
            $_SESSION['role'] = $row['role'];       
            $_SESSION['full_name'] = $row['full_name']; 
            $_SESSION['username'] = $row['full_name']; // Using full_name as username
            
            // --- 3. REDIRECT BASED ON ROLE ---
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
            <button type="button" class="forgot-btn" onclick="window.location.href='forgot_password.php'">Forgot password?</button>
        </form>

        <div class="footer">
            <p>Don't have an account?</p>
            <button onclick="window.location.href='register.php'">Create new account</button>
        </div>
    </div>

</body>
</html>