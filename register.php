<?php
 include 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $studentId = $_POST['studentId'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (full_name, email, student_id, role, phone, password) 
            VALUES ('$fullName', '$email', '$studentId', '$role', '$phone', '$hashedPassword')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

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
                    <option value="Librarian">Librarian</option>
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
            <p>After submitting, please visit the library with a valid ID for account verification and activation.</p>
        </div>
    </div>

</body>
</html>
