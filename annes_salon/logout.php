<?php
// ============================================================
// logout.php
// Destroys the user session and redirects to login
// ============================================================

session_start();
session_destroy();
header('Location: login.php');
exit;
?>