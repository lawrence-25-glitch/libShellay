<?php
session_start();
include 'db.php'; // Using your existing db connection

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// 2. HELPER: ADD NOTIFICATION
function createNotification($conn, $u_id, $msg) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $u_id, $msg);
    $stmt->execute();
}

// 3. HANDLE ACTIONS (Borrow / Reserve)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // BORROW LOGIC
    if (isset($_POST['borrow_book_id'])) {
        $book_id = $_POST['borrow_book_id'];
        
        // Check stock
        $stmt = $conn->prepare("SELECT title, author, quantity FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();

        if ($book && $book['quantity'] > 0) {
            // A. Create Transaction (Borrowed)
            $date = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, book_id, date_borrowed, status) VALUES (?, ?, ?, 'Borrowed')");
            $stmt->bind_param("iis", $userId, $book_id, $date);
            $stmt->execute();

            // B. Decrease Stock
            $conn->query("UPDATE books SET quantity = quantity - 1 WHERE book_id = $book_id");

            $_SESSION['flash_message'] = "✅ Success! You borrowed: " . $book['title'];
            header("Location: teacher_dashboard.php");
            exit();
        }
    }

    // RESERVE LOGIC
    if (isset($_POST['reserve_book_id'])) {
        $book_id = $_POST['reserve_book_id'];
        
        // Get Title
        $stmt = $conn->prepare("SELECT title FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $title = $stmt->get_result()->fetch_assoc()['title'];

        // A. Create Transaction (Reserved)
        $date = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, book_id, date_reserved, status) VALUES (?, ?, ?, 'Reserved')");
        $stmt->bind_param("iis", $userId, $book_id, $date);
        $stmt->execute();

        // B. Send Notification
        createNotification($conn, $userId, "Reservation confirmed for '$title'. Please pick it up within 24 hours.");

        $_SESSION['flash_message'] = "🔖 Reserved: " . $title;
        header("Location: teacher_dashboard.php");
        exit();
    }
}

// 4. FETCH DATA FOR DASHBOARD
// Inventory
$books = $conn->query("SELECT * FROM books ORDER BY title ASC");

// My Active Books Count
$countSql = "SELECT COUNT(*) as total FROM transactions WHERE user_id = $userId AND status IN ('Borrowed', 'Reserved')";
$myBooksCount = $conn->query($countSql)->fetch_assoc()['total'];

// Notifications
$notifSql = "SELECT * FROM notifications WHERE user_id = $userId ORDER BY id DESC";
$notifications = $conn->query($notifSql);
$notifCount = $notifications->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Teacher Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="SD.css"> <style>
    /* Custom Styles for Teacher Dashboard */
    .btn-borrow { background-color: #10b981; color: white; border:none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
    .btn-reserve { background-color: #f59e0b; color: white; border:none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
    .btn-borrow:hover, .btn-reserve:hover { opacity: 0.9; }
    
    /* Notification Badge */
    .badge { background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px; vertical-align: middle; }
    
    /* Modal Styles */
    .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 12px; width: 80%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
    .close-modal { float: right; font-size: 24px; cursor: pointer; color: #aaa; }
    .close-modal:hover { color: #000; }
    .notif-list { list-style: none; padding: 0; margin-top: 15px; }
    .notif-item { padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; color: #333; }
    .notif-time { display: block; font-size: 11px; color: #888; margin-top: 4px; }

    /* Toast Notification */
    .toast { position: fixed; bottom: 20px; right: 20px; background: #fff; border-left: 5px solid #10b981; padding: 15px 25px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); animation: slideIn 0.5s ease; z-index: 1000; }
    @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }
  </style>
</head>
<body>

<div class="app">
    <aside class="sidebar">
      <section class="user">
        <div class="brand">
            <div class="brand-logo"></div>
        </div>
        <br>
        <h2><?php echo htmlspecialchars($userName); ?></h2>
        <p>Faculty • Teacher</p>
      </section>

      <nav class="nav">
        <a href="teacher_dashboard.php">📊 Dashboard</a>
        <a href="#" onclick="openModal()">
            🔔 Notifications 
            <?php if($notifCount > 0) echo "<span class='badge'>$notifCount</span>"; ?>
        </a>
        <a href="logout.php" class="signout">🚪 Sign Out</a>
      </nav>
    </aside>

    <main>
      <header class="header">
        <h2>Teacher Library Portal</h2>
      </header>

      <div class="content">
        <section class="metrics">
          <div class="card primary">
            <div class="label">My Active Books</div>
            <div class="value"><?php echo $myBooksCount; ?></div>
            <div class="note">Unlimited borrowing privilege</div>
          </div>
          <div class="card success">
            <div class="label">Library Status</div>
            <div class="value">Open</div>
            <div class="note">Closes at 5:00 PM</div>
          </div>
          <div class="card warning">
            <div class="label">Unread Alerts</div>
            <div class="value"><?php echo $notifCount; ?></div>
            <div class="note">Check notifications</div>
          </div>
        </section>

        <section class="panels">
          <div class="panel">
            <div class="panel-header">
              <h3>📚 Library Catalog</h3>
            </div>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Availability</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if ($books->num_rows > 0) {
                      while($row = $books->fetch_assoc()) {
                          $qty = intval($row['quantity']);
                  ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>
                            <?php if($qty > 0): ?>
                                <span style="color:#10b981; font-weight:bold;">Available (<?php echo $qty; ?>)</span>
                            <?php else: ?>
                                <span style="color:#ef4444; font-weight:bold;">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <?php if($qty > 0): ?>
                                    <input type="hidden" name="borrow_book_id" value="<?php echo $row['book_id']; ?>">
                                    <button type="submit" class="btn-borrow">Borrow</button>
                                <?php else: ?>
                                    <input type="hidden" name="reserve_book_id" value="<?php echo $row['book_id']; ?>">
                                    <button type="submit" class="btn-reserve">Reserve</button>
                                <?php endif; ?>
                            </form>
                        </td>
                      </tr>
                  <?php 
                      }
                  } else {
                      echo "<tr><td colspan='5' class='empty'>No books found in the library.</td></tr>";
                  }
                  ?>
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
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <h3>🔔 Your Notifications</h3>
    <ul class="notif-list">
        <?php 
        if ($notifCount > 0) {
            // Reset pointer to start of result set
            $notifications->data_seek(0); 
            while($notif = $notifications->fetch_assoc()) {
                echo "<li class='notif-item'>";
                echo htmlspecialchars($notif['message']);
                echo "<span class='notif-time'>" . $notif['created_at'] . "</span>";
                echo "</li>";
            }
        } else {
            echo "<li class='notif-item'>No new notifications.</li>";
        }
        ?>
    </ul>
  </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="toast" id="toast">
        <?php echo $_SESSION['flash_message']; ?>
    </div>
    <script>
        setTimeout(() => {
            const t = document.getElementById('toast');
            if(t) { t.style.opacity='0'; setTimeout(()=>t.remove(),500); }
        }, 3000);
    </script>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<script>
    function openModal() { document.getElementById('notifModal').style.display = "block"; }
    function closeModal() { document.getElementById('notifModal').style.display = "none"; }
    
    // Close modal if user clicks outside of it
    window.onclick = function(event) {
        if (event.target == document.getElementById('notifModal')) {
            closeModal();
        }
    }
</script>

</body>
</html>