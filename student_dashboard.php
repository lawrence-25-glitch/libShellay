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
    // Default is_read = 0
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)");
    $stmt->bind_param("is", $uid, $msg);
    $stmt->execute();
}

// 3. HANDLE RESERVATION (Limit: 3 Books)
$countSql = "SELECT COUNT(*) as total FROM transactions WHERE user_id = $userId AND status IN ('Borrowed', 'Reserved')";
$current_borrows = $conn->query($countSql)->fetch_assoc()['total'];

if (isset($_POST['reserve_book'])) {
    $book_id = $_POST['book_id'];

    if ($current_borrows >= 3) {
        echo "<script>alert('🚫 LIMIT REACHED: You cannot have more than 3 active books.');</script>";
    } else {
        // Check stock
        $stmt = $conn->prepare("SELECT title, quantity FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $check = $stmt->get_result()->fetch_assoc();
        
        if ($check && $check['quantity'] > 0) {
            $today = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime('+7 days')); 

            $stmt = $conn->prepare("INSERT INTO transactions (user_id, book_id, date_reserved, due_date, status) VALUES (?, ?, ?, ?, 'Reserved')");
            $stmt->bind_param("iiss", $userId, $book_id, $today, $due_date);
            
            if ($stmt->execute()) {
                $conn->query("UPDATE books SET quantity = quantity - 1 WHERE book_id = $book_id");
                addNotification($conn, $userId, "You reserved '{$check['title']}'. Must return by: $due_date");
                
                $_SESSION['flash_message'] = "✅ Reserved: " . $check['title'];
                header("Location: student_dashboard.php");
                exit();
            }
        } else {
             $_SESSION['flash_message'] = "❌ Error: Book is out of stock.";
        }
    }
}

// 4. FETCH DATA
$books = $conn->query("SELECT * FROM books WHERE status = 'active'");

// --- NOTIFICATION LOGIC ---
$unreadSql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = $userId AND is_read = 0";
$notifCount = $conn->query($unreadSql)->fetch_assoc()['unread'];

$listSql = "SELECT * FROM notifications WHERE user_id = $userId ORDER BY id DESC LIMIT 10";
$notifications = $conn->query($listSql);

// --- PENALTY CALCULATION ---
$penalty = 0;
$today_str = date('Y-m-d');

$penSql = "SELECT t.due_date, b.price 
           FROM transactions t 
           JOIN books b ON t.book_id = b.book_id 
           WHERE t.user_id = $userId AND t.status = 'Borrowed'";

$penResult = $conn->query($penSql);

while($row = $penResult->fetch_assoc()) {
    if ($row['due_date'] && $today_str > $row['due_date']) {
        $penalty += $row['price'];
    }
}
$penalty_display = "₱" . number_format($penalty, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Student Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="SD.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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
        <a href="#" onclick="openModal('notifModal')">
            🔔 Notifications 
            <span class='badge' id="navBadge" style="<?php echo ($notifCount > 0) ? '' : 'display:none;'; ?>">
                <?php echo $notifCount; ?>
            </span>
        </a>
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
            <div class="value"><?php echo $penalty_display; ?></div>
            <div class="note">Pay at counter</div>
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
              <h3>📚 Available Books</h3>
            </div>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr><th>Title</th><th>Author</th><th>Category</th><th>Price</th><th>Stock</th><th>Action</th></tr>
                </thead>
                <tbody>
                  <?php 
                  if ($books->num_rows > 0) {
                      while($row = $books->fetch_assoc()) {
                          $qty = intval($row['quantity']);
                  ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>₱<?php echo number_format($row['price'], 2); ?></td>
                        <td>
                            <?php if ($qty > 0) echo "<span style='color:#10b981; font-weight:bold'>($qty)</span>"; 
                                  else echo "<span style='color:#ef4444; font-weight:bold'>Out of Stock</span>"; ?>
                        </td>
                        <td>
                            <?php if ($qty > 0) { ?>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                                <button type="submit" name="reserve_book" class="btn-reserve">Reserve</button>
                            </form>
                            <?php } else { echo "Unavailable"; } ?>
                        </td>
                      </tr>
                  <?php 
                      } 
                  } else { echo "<tr><td colspan='6'>No books available.</td></tr>"; }
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
      <span class="close" onclick="closeModal('notifModal')">&times;</span>
      <h3>🔔 Notifications</h3>
      <ul class="notif-list">
        <?php 
        if ($notifications->num_rows > 0) {
            while ($notif = $notifications->fetch_assoc()) {
                $bg = ($notif['is_read'] == 0) ? "background:#f0f9ff;" : "";
                echo "<li class='notif-item' style='$bg'>";
                echo htmlspecialchars($notif['message']);
                echo "<br><span class='notif-date'>" . $notif['created_at'] . "</span>";
                echo "</li>";
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
            <thead><tr><th>Title</th><th>Price</th><th>Status</th><th>Due Date</th></tr></thead>
            <tbody>
                <?php
                $mySql = "SELECT b.title, b.price, t.status, t.due_date 
                          FROM transactions t 
                          JOIN books b ON t.book_id = b.book_id 
                          WHERE t.user_id = $userId AND t.status IN ('Borrowed','Reserved')";
                $myRes = $conn->query($mySql);
                if ($myRes->num_rows > 0) {
                    while($row = $myRes->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['title']}</td>";
                        echo "<td>₱" . number_format($row['price'], 2) . "</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "<td>" . ($row['due_date'] ? $row['due_date'] : 'N/A') . "</td>";
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='4'>No active books.</td></tr>"; }
                ?>
            </tbody>
        </table>
      </div>
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
    // --- 1. EXISTING MODAL LOGIC ---
    function openModal(modalId) { 
        document.getElementById(modalId).style.display = "block"; 
        
        if (modalId === 'notifModal') {
            const navBadge = document.getElementById('navBadge');
            const cardBadge = document.getElementById('cardBadge');
            
            // Hide Badges immediately
            if(navBadge) navBadge.style.display = 'none';
            if(cardBadge) cardBadge.innerText = '0';
            
            // Mark as read in DB
            fetch('mark_as_read.php');
        }
    }

    function closeModal(modalId) { 
        document.getElementById(modalId).style.display = "none"; 
    }
    
    window.onclick = function(e) { 
        if(e.target.classList.contains('modal')) {
            e.target.style.display = "none";
        }
    }

    // --- 2. NEW AUTOMATIC UPDATE LOGIC (POLLING) ---
    function checkNotifications() {
        // Calls the new PHP file we created
        fetch('get_unread_count.php')
            .then(response => response.json())
            .then(data => {
                const count = data.count;
                const navBadge = document.getElementById('navBadge');
                const cardBadge = document.getElementById('cardBadge');

                // Update the Dashboard Card
                if (cardBadge) cardBadge.innerText = count;

                // Update the Sidebar Badge
                if (navBadge) {
                    navBadge.innerText = count;
                    if (count > 0) {
                        navBadge.style.display = 'inline-block'; // Show if unread
                    } else {
                        navBadge.style.display = 'none'; // Hide if 0
                    }
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
    }

    // Run this function every 3000 milliseconds (3 seconds)
    setInterval(checkNotifications, 3000);
  </script>
</body>
</html>