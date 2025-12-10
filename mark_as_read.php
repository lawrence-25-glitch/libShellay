<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // Update all notifications for this user to "Read" (1)
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}
?>
<script>
    function openModal() { 
        document.getElementById('notifModal').style.display = "block"; 
        
        // 1. Visually hide the red badge immediately
        const badges = document.querySelectorAll('.badge');
        badges.forEach(b => b.style.display = 'none');

        // 2. Update the "Unread Alerts" card number to 0
        const cardValue = document.querySelector('.card.warning .value');
        if(cardValue) cardValue.innerText = '0';

        // 3. Send a background signal to the database to mark them as read
        fetch('mark_as_read.php'); 
    }

    function closeModal() { 
        document.getElementById('notifModal').style.display = "none"; 
    }
    
    // ... rest of your script ...
</script>