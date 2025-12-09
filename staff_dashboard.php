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
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $uid, $msg);
    $stmt->execute();
}

// ISSUE BOOK
if (isset($_POST['issue_book'])) {
    $trans_id = $_POST['transaction_id'];
    $date = date('Y-m-d');
    $stmt = $conn->prepare("UPDATE transactions SET status = 'Borrowed', date_borrowed = ? WHERE id = ?");
    $stmt->bind_param("si", $date, $trans_id);
    if ($stmt->execute()) header("Refresh:0");
}

// RETURN BOOK
if (isset($_POST['return_book'])) {
    $trans_id = $_POST['transaction_id'];
    $book_id = $_POST['book_id_ref'];
    $student_id = $_POST['student_id']; // For notification
    $date = date('Y-m-d');

    $stmt = $conn->prepare("UPDATE transactions SET status = 'Returned', date_returned = ? WHERE id = ?");
    $stmt->bind_param("si", $date, $trans_id);
    
    if ($stmt->execute()) {
        $conn->query("UPDATE books SET quantity = quantity + 1 WHERE book_id = $book_id");
        // Notify student
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
$notifCount = $notifications->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Staff Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="SD.css">
  <style>
    /* Button Colors */
    .btn.issue { background-color: #10b981; border: none; color: white; } 
    .btn.return { background-color: #ef4444; border: none; color: white; }
    .staff-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 1000px) { .staff-layout { grid-template-columns: 1fr; } }
    
    /* Modal Styles */
    .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
    .close { float: right; font-size: 24px; cursor: pointer; }
    .badge { background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px; }
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
        <a href="#" onclick="openModal()">🔔 Notifications <?php if($notifCount > 0) echo "<span class='badge'>$notifCount</span>"; ?></a>
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
                <div class="panel-header"><h3>📍 Issue Book</h3></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Student</th><th>Book</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php
                        $res = $conn->query("SELECT t.id, u.full_name, b.title FROM transactions t JOIN users u ON t.user_id = u.userID JOIN books b ON t.book_id = b.book_id WHERE t.status = 'Reserved'");
                        while($row = $res->fetch_assoc()) {
                            echo "<tr><td>{$row['full_name']}</td><td>{$row['title']}</td>
                            <td><form method='POST'><input type='hidden' name='transaction_id' value='{$row['id']}'><button type='submit' name='issue_book' class='btn issue'>Issue</button></form></td></tr>";
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
                            echo "<tr><td>{$row['full_name']}</td><td>{$row['title']}</td>
                            <td><form method='POST'>
                            <input type='hidden' name='transaction_id' value='{$row['id']}'>
                            <input type='hidden' name='book_id_ref' value='{$row['book_id']}'>
                            <input type='hidden' name='student_id' value='{$row['user_id']}'>
                            <button type='submit' name='return_book' class='btn return'>Return</button>
                            </form></td></tr>";
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
      <ul style="padding:0; list-style:none; margin-top:10px;">
        <?php foreach ($notifications as $n) { echo "<li style='padding:10px; border-bottom:1px solid #eee;'>".htmlspecialchars($n['message'])."</li>"; } ?>
      </ul>
    </div>
  </div>

  <script>
    function openModal() { document.getElementById('notifModal').style.display = "block"; }
    function closeModal() { document.getElementById('notifModal').style.display = "none"; }
    window.onclick = function(e) { if(e.target.id == 'notifModal') closeModal(); }
  </script>
</body>
</html>