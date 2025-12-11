<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// --- 1. ADD BOOK LOGIC ---
if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $cat = $_POST['category'];
    $qty = $_POST['quantity'];
    $isbn = $_POST['isbn'];
    $price = $_POST['price'];
    $date = date('Y-m-d');
    
    $stmt = $conn->prepare("INSERT INTO books (title, author, category, quantity, ISBN, price, status, date_added) VALUES (?, ?, ?, ?, ?, ?, 'active', ?)");
    $stmt->bind_param("sssisds", $title, $author, $cat, $qty, $isbn, $price, $date);
    
    if($stmt->execute()) { echo "<script>alert('✅ Book Added Successfully!');</script>"; } 
    else { echo "<script>alert('❌ Error: " . $stmt->error . "');</script>"; }
}

// METRICS
$totalBooks = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$lowStock = $conn->query("SELECT COUNT(*) as total FROM books WHERE quantity < 5")->fetch_assoc()['total'];

// --- 2. NEW: FETCH PENDING USERS ---
$pendingUsers = $conn->query("SELECT * FROM users WHERE status = 'pending' ORDER BY userID ASC");
$pendingCount = $pendingUsers->num_rows;

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
    body { font-family: 'Poppins', sans-serif; }
    .form-group { margin-bottom: 10px; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; outline: none; }
    .form-group input:focus { border-color: #10b981; }
    .row-inputs { display: flex; gap: 10px; }
    
    /* APPROVE/REJECT BUTTONS */
    .btn-approve { background: #10b981; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
    .btn-reject { background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
    .btn-approve:hover { background: #059669; }
    .btn-reject:hover { background: #dc2626; }
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
          <div class="card success"><div class="label">Pending Approvals</div><div class="value"><?php echo $pendingCount; ?></div></div>
        </section>

        <?php if ($pendingCount > 0): ?>
        <section class="panels" style="margin-bottom: 20px;">
            <div class="panel" style="border: 2px solid #f59e0b;">
                <div class="panel-header" style="background: #fffbeb;">
                    <h3 style="color:#b45309">📝 Pending Registrations</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php while($usr = $pendingUsers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usr['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($usr['email']); ?></td>
                                <td><strong><?php echo htmlspecialchars($usr['role']); ?></strong></td>
                                <td>
                                    <form action="user_action.php" method="POST" style="display:flex; gap:5px;">
                                        <input type="hidden" name="user_id" value="<?php echo $usr['userID']; ?>">
                                        <button type="submit" name="approve" class="btn-approve">Approve</button>
                                        <button type="submit" name="reject" class="btn-reject" onclick="return confirm('Reject and delete this user?');">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="panels" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
          <div class="panel">
            <div class="panel-header"><h3>➕ Add New Book</h3></div>
            <div class="panel-body">
                <form method="POST">
                    <div class="form-group"><input type="text" name="title" placeholder="Book Title" required></div>
                    <div class="form-group"><input type="text" name="author" placeholder="Author Name" required></div>
                    <div class="row-inputs">
                        <div class="form-group" style="flex:1"><input type="text" name="category" placeholder="Category" required></div>
                        <div class="form-group" style="flex:1"><input type="number" name="quantity" placeholder="Qty" required></div>
                    </div>
                    <div class="row-inputs">
                        <div class="form-group" style="flex:1"><input type="text" name="isbn" placeholder="ISBN" required></div>
                        <div class="form-group" style="flex:1"><input type="number" step="0.01" name="price" placeholder="Price" required></div>
                    </div>
                    <button type="submit" name="add_book" class="btn primary" style="width:100%">Add Book</button>
                </form>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header"><h3>📦 Recent Inventory</h3></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Title</th><th>Author</th><th>ISBN</th><th>Price</th><th>Stock</th></tr></thead>
                    <tbody>
                    <?php
                    $inv = $conn->query("SELECT * FROM books ORDER BY book_id DESC LIMIT 10");
                    while($row = $inv->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ISBN']) . "</td>"; 
                        echo "<td>₱" . number_format($row['price'], 2) . "</td>"; 
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
                echo "<li style='padding:10px; border-bottom:1px solid #eee; $bg'>".htmlspecialchars($n['message'])."</li>"; 
            }
        } else { echo "<li>No notifications.</li>"; }
        ?>
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