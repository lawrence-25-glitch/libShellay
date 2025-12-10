<?php
session_start();
include 'db.php'; 

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// 2. HELPER: ADD NOTIFICATION FUNCTION
function createNotification($conn, $u_id, $msg) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)");
    $stmt->bind_param("is", $u_id, $msg);
    $stmt->execute();
}

// 3. HANDLE ACTIONS (Borrow / Reserve)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // BORROW LOGIC
    if (isset($_POST['borrow_book_id'])) {
        $book_id = $_POST['borrow_book_id'];
        
        // Check stock
        $stmt = $conn->prepare("SELECT title, quantity FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();

        if ($book && $book['quantity'] > 0) {
            $date = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, book_id, date_borrowed, status) VALUES (?, ?, ?, 'Borrowed')");
            $stmt->bind_param("iis", $userId, $book_id, $date);
            $stmt->execute();

            $conn->query("UPDATE books SET quantity = quantity - 1 WHERE book_id = $book_id");

            $_SESSION['flash_message'] = "✅ Success! You borrowed: " . $book['title'];
            header("Location: teacher_dashboard.php");
            exit();
        }
    }

    // RESERVE LOGIC
    if (isset($_POST['reserve_book_id'])) {
        $book_id = $_POST['reserve_book_id'];
        
        $stmt = $conn->prepare("SELECT title FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $title = $stmt->get_result()->fetch_assoc()['title'];

        $date = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, book_id, date_reserved, status) VALUES (?, ?, ?, 'Reserved')");
        $stmt->bind_param("iis", $userId, $book_id, $date);
        $stmt->execute();

        // Send Notification (is_read will be 0 by default)
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

// --- NOTIFICATION LOGIC FIX ---
// A. Count ONLY unread messages for the Red Badge
$unreadSql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = $userId AND is_read = 0";
$notifCount = $conn->query($unreadSql)->fetch_assoc()['unread'];

// B. Get ALL messages (Read and Unread) for the Modal List
$listSql = "SELECT * FROM notifications WHERE user_id = $userId ORDER BY id DESC LIMIT 10";
$notifications = $conn->query($listSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Teacher Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="SD.css"> 
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    
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
            <?php if($notifCount > 0): ?>
                <span class='badge' id="navBadge"><?php echo $notifCount; ?></span>
            <?php endif; ?>
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
            <div class="value" id="cardBadge"><?php echo $notifCount; ?></div>
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
                                <span style="color:#10b981; font-weight:bold;">(<?php echo $qty; ?>)</span>
                            <?php else: ?>
                                <span style="color:#ef4444; font-weight:bold;">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <?php if($qty > 0): ?>
                                    <input type="hidden" name="borrow_book_id" value="<?php echo $row['book_id']; ?>">
                                    <button type="submit" class="btn-borrow">Reserve</button>
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
        if ($notifications->num_rows > 0) {
            // No need to data_seek here if we are just iterating once
            while($notif = $notifications->fetch_assoc()) {
                // Style unread messages differently (optional)
                $bg = ($notif['is_read'] == 0) ? "background:#f0f9ff;" : "";
                
                echo "<li class='notif-item' style='$bg'>";
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
    // --- THIS IS THE CRITICAL JAVASCRIPT PART ---
    function openModal() { 
        document.getElementById('notifModal').style.display = "block"; 
        
        // 1. Visually hide the red badge in the Sidebar
        const navBadge = document.getElementById('navBadge');
        if(navBadge) navBadge.style.display = 'none';

        // 2. Update the "Unread Alerts" card number to 0
        const cardBadge = document.getElementById('cardBadge');
        if(cardBadge) cardBadge.innerText = '0';

        // 3. Send background signal to mark_as_read.php
        fetch('mark_as_read.php')
            .then(response => console.log('Notifications marked as read'))
            .catch(error => console.error('Error:', error));
    }

    function closeModal() { 
        document.getElementById('notifModal').style.display = "none"; 
    }
    
    // Close modal if user clicks outside of it
    window.onclick = function(event) {
        if (event.target == document.getElementById('notifModal')) {
            closeModal();
        }
    }
</script>

</body>
</html>