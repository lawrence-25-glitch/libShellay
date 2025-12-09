<?php
session_start();

// --- CONFIGURATION ---
// Connect to database (Adjust generic "root" and "" if you have a password)
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$step = 1; // Default step: Search for email
$message = "";
$simulate_email_link = "";

// --- LOGIC HANDLER ---

// 1. HANDLE SEARCH FORM (User enters email)
if (isset($_POST['search_email'])) {
    $email = $_POST['email'];
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate Token
        $token = bin2hex(random_bytes(50));
        
        // Save token to DB (Expires in 1 hour)
        $update = $conn->prepare("UPDATE users SET reset_token=?, token_expire=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email=?");
        $update->bind_param("ss", $token, $email);
        $update->execute();

        // SIMULATE EMAIL (Since you are on localhost)
        // This creates a link back to THIS file, but with the token in the URL
        $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";
        $reset_link = $current_url . "?token=" . $token . "&email=" . $email;

        $step = 2; // Move to "Check Email" view
        $simulate_email_link = $reset_link;
    } else {
        $message = "❌ No account found with that email address.";
    }
}

// 2. HANDLE NEW PASSWORD SUBMIT (User types new password)
if (isset($_POST['reset_password'])) {
    $new_pass = $_POST['password'];
    $email = $_POST['email'];
    $token = $_POST['token'];

    // Verify token is still valid
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND reset_token=? AND token_expire > NOW()");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Hash and Update
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expire=NULL WHERE email=?");
        $update->bind_param("ss", $hashed, $email);
        
        if ($update->execute()) {
            echo "<script>alert('Password Reset Successfully! Login now.'); window.location.href='login.php';</script>";
            exit();
        }
    } else {
        $message = "❌ Session expired or invalid link.";
    }
}

// 3. HANDLE TOKEN CLICK (User clicked the link)
if (isset($_GET['token'])) {
    $step = 3; // Move to "New Password" view
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; color: #1c1e21; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 100%; max-width: 450px; text-align: center; }
        h2 { margin-top: 0; font-size: 22px; margin-bottom: 10px; }
        p { color: #606770; font-size: 15px; margin-bottom: 20px; line-height: 1.4; }
        input { width: 100%; padding: 14px 16px; margin: 10px 0; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #1877f2; box-shadow: 0 0 0 2px #e7f3ff; }
        
        .btn { width: 100%; padding: 12px; background: #1877f2; color: white; border: none; border-radius: 6px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-top: 10px; }
        .btn:hover { background: #166fe5; }
        .btn-cancel { background: #e4e6eb; color: #4b4f56; margin-top: 10px; }
        .btn-cancel:hover { background: #d8dadf; }
        
        .alert { background: #ffebe8; color: #c0392b; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #ffcece; }
        .sim-email { background: #e8f5e9; border: 2px dashed #4caf50; padding: 15px; margin-top: 20px; text-align: left; }
        .sim-email a { color: #fff; background: #4caf50; padding: 8px 12px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="card">
        
        <?php if($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if($step == 1): ?>
            <h2>Find Your Account</h2>
            <p>Please enter your email address to search for your account.</p>
            <form method="POST">
                <input type="email" name="email" placeholder="Email address" 
                value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" required>
                <button type="submit" name="search_email" class="btn">Search</button>
                <button type="button" class="btn btn-cancel" onclick="window.location.href='login.php'">Cancel</button>
            </form>
        <?php endif; ?>

        <?php if($step == 2): ?>
            <h2>Check Your Email</h2>
            <p>We have sent a code to your email address.</p>
            
            <div class="sim-email">
                <strong>🖥️ Localhost Developer Mode:</strong><br>
                <small>Since emails don't work on XAMPP by default, click this button to simulate clicking the email link:</small><br>
                <a href="<?php echo $simulate_email_link; ?>">RESET PASSWORD NOW</a>
            </div>

            <button type="button" class="btn btn-cancel" onclick="window.location.href='login.php'">Back to Login</button>
        <?php endif; ?>

        <?php if($step == 3): ?>
            <h2>Choose a New Password</h2>
            <p>Create a new password that is at least 6 characters long.</p>
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
                
                <input type="password" name="password" placeholder="New Password" required minlength="6">
                <button type="submit" name="reset_password" class="btn">Continue</button>
            </form>
        <?php endif; ?>

    </div>

</body>
</html>