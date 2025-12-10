<?php
session_start();
include 'db.php';

// 1. SECURITY & SESSION
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// 2. HELPER: NOTIFICATION
function addNotification($conn, $uid, $msg) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $uid, $msg);
    $stmt->execute();
}

// 3. HANDLE RESERVATION (Limit: 3 Books)
// First, count how many books this student has (Borrowed OR Reserved)
$countSql = "SELECT COUNT(*) as total FROM transactions WHERE user_id = $userId AND status IN ('Borrowed', 'Reserved')";
$current_borrows = $conn->query($countSql)->fetch_assoc()['total'];

// --- FIX: This PHP block now matches the HTML form below ---
if (isset($_POST['reserve_book'])) {
    $book_id = $_POST['book_id'];

    if ($current_borrows >= 3) {
        echo "<script>alert('🚫 LIMIT REACHED: You cannot have more than 3 active books.');</script>";
    } else {
        // Check stock
        $check = $conn->query("SELECT title, quantity FROM books WHERE book_id = $book_id")->fetch_assoc();
        
        if ($check['quantity'] > 0) {
            $date = date('Y-m-d');
            
            // A. Create Transaction (Status = Reserved)
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, book_id, date_reserved, status) VALUES (?, ?, ?, 'Reserved')");
            $stmt->bind_param("iis", $userId, $book_id, $date);
            
            if ($stmt->execute()) {
                // B. Decrease Stock
                $conn->query("UPDATE books SET quantity = quantity - 1 WHERE book_id = $book_id");
                
                // C. Notify User
                addNotification($conn, $userId, "You reserved '{$check['title']}'. Please pick it up within 24 hours.");
                
                // Refresh page
                echo "<script>window.location.href='student_dashboard.php';</script>";
            }
        } else {
            echo "<script>alert('❌ Error: Book is out of stock.');</script>";
        }
    }
}

// 4. FETCH DATA
$notifSql = "SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC";
$notifications = $conn->query($notifSql);
$notifCount = $notifications->num_rows;

// Calculate Penalty (Placeholder)
$penalty = "₱0.00";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Student Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="SD.css">
  <style>
    /* Modal & Notification Styles */
    .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
    .close { float: right; font-size: 24px; cursor: pointer; }
    .notif-list { list-style: none; padding: 0; margin-top: 10px; max-height: 300px; overflow-y: auto; }
    .notif-item { padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
    .notif-date { font-size: 11px; color: #888; display: block; margin-top: 4px; }
    .badge { background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px; vertical-align: middle; }
    
    /* Button Style Fix */
    .btn-reserve { background-color: #f59e0b; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
    .btn-reserve:hover { background-color: #d97706; }
  </style>
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <section class="user">
        <div class="brand"><div class="brand-logo"></div></div>
        <br>
        <h2><?php echo htmlspecialchars($userName); ?></h2>
        <p>Student • Semester 2</p>
      </section>
      <nav class="nav">
        <a href="student_dashboard.php">📊 Dashboard</a>
        <a href="#" onclick="openModal('notifModal')">🔔 Notifications <?php if($notifCount > 0) echo "<span class='badge'>$notifCount</span>"; ?></a>
        <a href="logout.php" class="signout">🚪 Sign Out</a>
      </nav>
    </aside>

    <main>
      <header class="header">
        <h2>Student Library</h2>
      </header>

      <div class="content">
        <section class="metrics">
          <div class="card primary" onclick="openModal('myBooksModal')" style="cursor:pointer;">
            <div class="label">My Books</div>
            <div class="value"><?php echo $current_borrows; ?> / 3</div>
            <div class="note">Click to view details</div>
          </div>
          <div class="card success">
            <div class="label">Reservations</div>
            <div class="value">Active</div>
            <div class="note">You can reserve more</div>
          </div>
          <div class="card warning">
            <div class="label">Outstanding Fees</div>
            <div class="value"><?php echo $penalty; ?></div>
            <div class="note">Pay at counter</div>
          </div>
        </section>

        <section class="panels">
          <div class="panel">
            <div class="panel-header">
              <h3>📚 Available Books</h3>
            </div>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr><th>Title</th><th>Author</th><th>Category</th><th>Stock</th><th>Action</th></tr>
                </thead>
                <tbody>
                  <?php 
                  $books = $conn->query("SELECT * FROM books WHERE status = 'active'");
                  while($row = $books->fetch_assoc()) {
                      $qty = intval($row['quantity']);
                  ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>
                            <?php if ($qty > 0) echo "<span style='color:green'>Available ($qty)</span>"; 
                                  else echo "<span style='color:red'>Out of Stock</span>"; ?>
                        </td>
                        <td>
                            <?php if ($qty > 0) { ?>
                            <form method="POST">
                                <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                                <button type="submit" name="reserve_book" class="btn-reserve">Reserve</button>
                            </form>
                            <?php } else { echo "Unavailable"; } ?>
                        </td>
                      </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <div id="notifModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('notifModal')">&times;</span>
      <h3>🔔 Notifications</h3>
      <ul class="notif-list">
        <?php 
        if ($notifCount > 0) {
            foreach ($notifications as $notif) {
                echo "<li class='notif-item'>".htmlspecialchars($notif['message'])."<br><span class='notif-date'>".$notif['created_at']."</span></li>";
            }
        } else { echo "<li class='notif-item'>No new alerts.</li>"; }
        ?>
      </ul>
    </div>
  </div>

  <div id="myBooksModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('myBooksModal')">&times;</span>
      <h3>📖 My Active Books</h3>
      <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                <?php
                $mySql = "SELECT b.title, t.status, t.date_reserved FROM transactions t JOIN books b ON t.book_id = b.book_id WHERE t.user_id = $userId AND t.status IN ('Borrowed','Reserved')";
                $myRes = $conn->query($mySql);
                if ($myRes->num_rows > 0) {
                    while($row = $myRes->fetch_assoc()) {
                        echo "<tr><td>{$row['title']}</td><td>{$row['status']}</td><td>{$row['date_reserved']}</td></tr>";
                    }
                } else { echo "<tr><td colspan='3'>No active books.</td></tr>"; }
                ?>
            </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function openModal(id) { document.getElementById(id).style.display = "block"; }
    function closeModal(id) { document.getElementById(id).style.display = "none"; }
    window.onclick = function(e) { if(e.target.classList.contains('modal')) e.target.style.display = "none"; }
  </script>
</body>
</html>