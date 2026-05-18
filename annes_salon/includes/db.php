<?php
// ============================================================
// includes/db.php
// PDO Database Connection
// ============================================================

$host   = 'localhost';
$dbname = 'annes_salon';   // <-- Change to your actual database name
$user   = 'root';          // <-- Change to your DB username
$pass   = '';              // <-- Change to your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Show a friendly error instead of leaking credentials
    die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
        <strong>Database connection failed.</strong><br>
        Please check your credentials in <code>includes/db.php</code>.<br><br>
        <em>' . htmlspecialchars($e->getMessage()) . '</em>
    </div>');
}
?>
