<?php
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture All Form Inputs
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $studentId = $_POST['studentId']; // Make sure your DB has this column
    $role = $_POST['role'];
    $phone = $_POST['phone'];         // Make sure your DB has this column
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // 2. Validate Passwords
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    // (Create connection if db.php didn't)
    if (!isset($conn)) {
        $conn = new mysqli("localhost", "root", "", "library");
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
    }

    // 3. Check for Duplicate Email
    $checkEmail = "SELECT email FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "<script>alert('This email is already registered!'); window.history.back();</script>";
        exit();
    }

    // 4. INSERT DATA (THE FIX)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // We explicitly add 'status' and set it to 'pending'
    // We also add 'student_id' and 'phone_number' to match your form
    $sql = "INSERT INTO users (full_name, email, student_id, role, phone_number, password, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: "ssssss" (6 strings)
    // Name, Email, ID, Role, Phone, Password
    $stmt->bind_param("ssssss", $fullName, $email, $studentId, $role, $phone, $hashedPassword);

    if ($stmt->execute()) {
        // 5. SUCCESS & REDIRECT
        echo "<script>
            alert('Registration Successful! Your account is pending approval.');
            window.location.href = 'landing_page.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Database Error: " . $stmt->error . "'); window.history.back();</script>";
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
    <title>Create Account - University Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <div class="header">
            <div class="emoji">📚</div>
            <h1>Create Account</h1>
            <p>Join University Library</p>
        </div>

        <form action="register.php" method="POST">
            <div>
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
            </div>

            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div>
                <label for="studentId">Student/Employee ID</label>
                <input type="text" id="studentId" name="studentId" placeholder="Enter your ID number" required>
            </div>

            <div>
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select your role</option>
                    <option value="Student">Student</option>
                    <option value="Teacher">Teacher</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>

            <div>
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>

            <div>
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
            </div>

            <div class="checkbox-container">
                <input type="checkbox" id="agreeTerms" required>
                <label for="agreeTerms">I agree to the library terms and conditions</label>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <div class="footer">
            <p>Already have an account?</p>
            <button onclick="window.location.href='login.php'">Sign In</button>
        </div>

        <div class="note">
            <p><strong>Note:</strong> Account registration requires approval.</p>
            <p>After submitting, please visit the library with a valid ID for account verification.</p>
        </div>
    </div>

</body>
</html>