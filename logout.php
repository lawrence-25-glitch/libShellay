<?php
session_start();
session_destroy();
header("Location: /Library-main/login.php");
exit();
?>
