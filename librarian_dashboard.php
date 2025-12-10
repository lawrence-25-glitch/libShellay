<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// --- 1. UPDATE: ADD BOOK LOGIC (Now includes ISBN, Price, and Date) ---
if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $cat = $_POST['category'];
    $qty = $_POST['quantity'];
    $isbn = $_POST['isbn'];      // New Field
    $price = $_POST['price'];    // New Field
    $date = date('Y-m-d');       // Auto-generate today's date
    
    // Updated Query to match your Database Image
    $stmt = $conn->prepare("INSERT INTO books (title, author, category, quantity, ISBN, price, status, date_added) VALUES (?, ?, ?, ?, ?, ?, 'active', ?)");
    
    // Types: s=string, i=int, d=decimal
    // title(s), author(s), category(s), quantity(i), ISBN(s), price(d), date(s)
    $stmt->bind_param("sssisds", $title, $author, $cat, $qty, $isbn, $price, $date);
    
    if($stmt->execute()) {
        echo "<script>alert('✅ Book Added Successfully!');</script>";
    } else {
        echo "<script>alert('❌ Error: " . $stmt->error . "');</script>";
    }
}

// METRICS
$totalBooks = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$lowStock = $conn->query("SELECT COUNT(*) as total FROM books WHERE quantity < 5")->fetch_assoc()['total'];

// NOTIFICATIONS
$notifSql = "SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC";
$notifications = $conn->query($notifSql);
$notifCount = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id = $userId AND is_read = 0")->fetch_assoc()['unread'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Librarian Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="SD.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    /* Consistent Styling */
    body { font-family: 'Poppins', sans-serif; }
    .form-group { margin-bottom: 10px; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; outline: none; }
    .form-group input:focus { border-color: #10b981; }
    
    .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
    .close { float: right; font-size: 24px; cursor: pointer; }
    
    .badge { background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px; vertical-align: middle; }
    
    /* Grid for double inputs */
    .row-inputs { display: flex; gap: 10px; }
  </style>
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <section class="user">
        <div class="brand"><div class="brand-logo"></div></div>
        <br><h2><?php echo htmlspecialchars($userName); ?></h2><p>Head Librarian</p>
      </section>
      <nav class="nav">
        <a href="librarian_dashboard.php">📊 Dashboard</a>
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
      <header class="header"><h2>Librarian Management</h2></header>
      <div class="content">
        <section class="metrics">
          <div class="card primary"><div class="label">Total Books</div><div class="value"><?php echo $totalBooks; ?></div></div>
          <div class="card warning"><div class="label">Low Stock Alerts</div><div class="value"><?php echo $lowStock; ?></div></div>
          <div class="card success"><div class="label">System Status</div><div class="value">Active</div></div>
        </section>

        <section class="panels" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
          
          <div class="panel">
            <div class="panel-header"><h3>➕ Add New Book</h3></div>
            <div class="panel-body">
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="title" placeholder="Book Title" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="author" placeholder="Author Name" required>
                    </div>
                    
                    <div class="row-inputs">
                        <div class="form-group" style="flex:1">
                             <input type="text" name="category" placeholder="Category" required>
                        </div>
                        <div class="form-group" style="flex:1">
                             <input type="number" name="quantity" placeholder="Qty" required>
                        </div>
                    </div>

                    <div class="row-inputs">
                        <div class="form-group" style="flex:1">
                            <input type="text" name="isbn" placeholder="ISBN (e.g. 978...)" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <input type="number" step="0.01" name="price" placeholder="Price (PHP)" required>
                        </div>
                    </div>

                    <button type="submit" name="add_book" class="btn primary" style="width:100%">Add Book</button>
                </form>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header"><h3>📦 Recent Inventory</h3></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Fetching data to verify fields are saving correctly
                    $inv = $conn->query("SELECT * FROM books ORDER BY book_id DESC LIMIT 10");
                    while($row = $inv->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ISBN']) . "</td>"; // Display ISBN
                        echo "<td>₱" . number_format($row['price'], 2) . "</td>"; // Display Price
                        echo "<td>" . $row['quantity'] . "</td>";
                        echo "</tr>";
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
      <span class="close" onclick="closeModal()">&times;</span>
      <h3>🔔 Notifications</h3>
      <ul style="padding:0; list-style:none; margin-top:10px; max-height:300px; overflow-y:auto;">
        <?php 
        if ($notifications->num_rows > 0) {
            foreach ($notifications as $n) { 
                $bg = ($n['is_read'] == 0) ? "background:#f0f9ff;" : "";
                echo "<li style='padding:10px; border-bottom:1px solid #eee; $bg'>";
                echo htmlspecialchars($n['message']);
                echo "<br><small style='color:#888'>".$n['created_at']."</small>";
                echo "</li>"; 
            }
        } else { echo "<li>No notifications.</li>"; }
        ?>
      </ul>
    </div>
  </div>

  <script>
    function openModal() { 
        document.getElementById('notifModal').style.display = "block"; 
        
        // Hide badge and mark as read (Consistency with other dashboards)
        const badge = document.getElementById('navBadge');
        if(badge) badge.style.display = 'none';
        
        // Ensure you have the mark_as_read.php file created from previous steps
        fetch('mark_as_read.php');
    }
    
    function closeModal() { document.getElementById('notifModal').style.display = "none"; }
    
    window.onclick = function(e) { if(e.target.id == 'notifModal') closeModal(); }
  </script>
</body>
</html>