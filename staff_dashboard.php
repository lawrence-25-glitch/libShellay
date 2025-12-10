<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// HELPER
function addNotification($conn, $uid, $msg) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)");
    $stmt->bind_param("is", $uid, $msg);
    $stmt->execute();
}

// --- 1. UPDATED ISSUE BOOK LOGIC ---
if (isset($_POST['issue_book'])) {
    $trans_id = $_POST['transaction_id'];
    $borrow_date = date('Y-m-d');
    
    // Get the manual due date from the form
    $due_date = $_POST['due_date']; 

    // Update Transaction: Set Status, Borrowed Date, AND Due Date
    $stmt = $conn->prepare("UPDATE transactions SET status = 'Borrowed', date_borrowed = ?, due_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $borrow_date, $due_date, $trans_id);
    
    if ($stmt->execute()) {
        header("Refresh:0");
    }
}

// RETURN BOOK
if (isset($_POST['return_book'])) {
    $trans_id = $_POST['transaction_id'];
    $book_id = $_POST['book_id_ref'];
    $student_id = $_POST['student_id']; 
    $date = date('Y-m-d');

    $stmt = $conn->prepare("UPDATE transactions SET status = 'Returned', date_returned = ? WHERE id = ?");
    $stmt->bind_param("si", $date, $trans_id);
    
    if ($stmt->execute()) {
        $conn->query("UPDATE books SET quantity = quantity + 1 WHERE book_id = $book_id");
        addNotification($conn, $student_id, "Your return has been processed. Thank you!");
        header("Refresh:0");
    }
}

// METRICS
$resCount = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE status='Reserved'")->fetch_assoc()['total'];
$borCount = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE status='Borrowed'")->fetch_assoc()['total'];

// NOTIFICATIONS
$notifSql = "SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC";
$notifications = $conn->query($notifSql);
$notifCount = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id = $userId AND is_read = 0")->fetch_assoc()['unread'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Staff Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="SD.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    /* Specific styles for the date input in the table */
    .date-input {
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: inherit;
        font-size: 13px;
        color: #333;
        outline: none;
    }
    .action-form {
        display: flex;
        gap: 10px;
        align-items: center;
    }
  </style>
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <section class="user">
        <div class="brand"><div class="brand-logo"></div></div>
        <br><h2><?php echo htmlspecialchars($userName); ?></h2><p>Staff • Active</p>
      </section>
      <nav class="nav">
        <a href="staff_dashboard.php">📊 Dashboard</a>
        <a href="#" onclick="openModal()">
            🔔 Notifications 
            <?php if($notifCount > 0): ?>
                <span class='badge' id="navBadge"><?php echo $notifCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="logout.php" class="signout">🚪 Sign Out</a>
      </nav>
    </aside>

    <main>
      <header class="header"><h2>Staff Dashboard</h2></header>
      <div class="content">
        <section class="metrics">
          <div class="card warning"><div class="label">Pending Issues</div><div class="value"><?php echo $resCount; ?></div></div>
          <div class="card primary"><div class="label">Active Borrows</div><div class="value"><?php echo $borCount; ?></div></div>
          <div class="card success"><div class="label">System Status</div><div class="value">Online</div></div>
        </section>

        <section class="panels">
          <div class="staff-layout">
            
            <div class="panel">
                <div class="panel-header"><h3>📍 Issue Book (Set Due Date)</h3></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Student</th><th>Book</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php
                        $res = $conn->query("SELECT t.id, u.full_name, b.title FROM transactions t JOIN users u ON t.user_id = u.userID JOIN books b ON t.book_id = b.book_id WHERE t.status = 'Reserved'");
                        
                        // Calculate default due date (e.g., Today + 7 days)
                        $defaultDue = date('Y-m-d', strtotime('+7 days'));

                        while($row = $res->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['full_name']}</td>";
                            echo "<td>{$row['title']}</td>";
                            echo "<td>
                                    <form method='POST' class='action-form'>
                                        <input type='hidden' name='transaction_id' value='{$row['id']}'>
                                        
                                        <input type='date' name='due_date' value='$defaultDue' required class='date-input'>
                                        
                                        <button type='submit' name='issue_book' class='btn issue'>Issue</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header"><h3>🔄 Return Book</h3></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Student</th><th>Book</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php
                        $bor = $conn->query("SELECT t.id, t.book_id, t.user_id, u.full_name, b.title FROM transactions t JOIN users u ON t.user_id = u.userID JOIN books b ON t.book_id = b.book_id WHERE t.status = 'Borrowed'");
                        while($row = $bor->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['full_name']}</td>";
                            echo "<td>{$row['title']}</td>";
                            echo "<td>
                                    <form method='POST'>
                                        <input type='hidden' name='transaction_id' value='{$row['id']}'>
                                        <input type='hidden' name='book_id_ref' value='{$row['book_id']}'>
                                        <input type='hidden' name='student_id' value='{$row['user_id']}'>
                                        <button type='submit' name='return_book' class='btn return'>Return</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

          </div>
        </section>
      </div>
    </main>
  </div>

  <div id="notifModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h3>🔔 Notifications</h3>
      <ul class="notif-list">
        <?php foreach ($notifications as $n) { 
             $bg = ($n['is_read'] == 0) ? "background:#f0f9ff;" : "";
             echo "<li class='notif-item' style='$bg'>".htmlspecialchars($n['message'])."</li>"; 
        } ?>
      </ul>
    </div>
  </div>

  <script>
    function openModal() { 
        document.getElementById('notifModal').style.display = "block";
        const badge = document.getElementById('navBadge');
        if(badge) badge.style.display = 'none';
        fetch('mark_as_read.php');
    }
    function closeModal() { document.getElementById('notifModal').style.display = "none"; }
    window.onclick = function(e) { if(e.target.id == 'notifModal') closeModal(); }
  </script>
</body>
</html>