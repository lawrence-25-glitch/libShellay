<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// ADD BOOK LOGIC
if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $cat = $_POST['category'];
    $qty = $_POST['quantity'];
    
    $stmt = $conn->prepare("INSERT INTO books (title, author, category, quantity, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->bind_param("sssi", $title, $author, $cat, $qty);
    if($stmt->execute()) echo "<script>alert('✅ Book Added!');</script>";
}

// METRICS
$totalBooks = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$lowStock = $conn->query("SELECT COUNT(*) as total FROM books WHERE quantity < 5")->fetch_assoc()['total'];

// NOTIFICATIONS
$notifSql = "SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC";
$notifications = $conn->query($notifSql);
$notifCount = $notifications->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Librarian Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="SD.css">
  <style>
    /* Form Styles matching the theme */
    .form-group { margin-bottom: 10px; }
    .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 12px; width: 90%; max-width: 500px; }
    .close { float: right; font-size: 24px; cursor: pointer; }
    .badge { background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px; }
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
        <a href="#" onclick="openModal()">🔔 Notifications <?php if($notifCount > 0) echo "<span class='badge'>$notifCount</span>"; ?></a>
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
                    <div class="form-group"><input type="text" name="title" placeholder="Book Title" required></div>
                    <div class="form-group"><input type="text" name="author" placeholder="Author" required></div>
                    <div class="form-group"><input type="text" name="category" placeholder="Category" required></div>
                    <div class="form-group"><input type="number" name="quantity" placeholder="Quantity" required></div>
                    <button type="submit" name="add_book" class="btn primary" style="width:100%">Add Book</button>
                </form>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header"><h3>📦 Inventory</h3></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Stock</th></tr></thead>
                    <tbody>
                    <?php
                    $inv = $conn->query("SELECT * FROM books ORDER BY book_id DESC LIMIT 10");
                    while($row = $inv->fetch_assoc()) {
                        echo "<tr><td>{$row['book_id']}</td><td>{$row['title']}</td><td>{$row['author']}</td><td>{$row['quantity']}</td></tr>";
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