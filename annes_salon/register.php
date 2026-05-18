<?php
// ============================================================
// register.php
// USER MANAGEMENT — Registration
// Prevents duplicate usernames
// ============================================================

session_start();
require_once 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username) || empty($full_name) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 4) {
        $error = 'Username must be at least 4 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check for duplicate username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) {
            $error = "The username \"$username\" is already taken. Please choose another.";
        } else {
            // Insert new user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, password, full_name) VALUES (:username, :password, :full_name)"
            );
            $stmt->execute([
                ':username'  => $username,
                ':password'  => $hashed,
                ':full_name' => $full_name,
            ]);
            $success = 'Account created successfully. You may now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anne's Salon — Register</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- FIX: was 'css/style.css' — corrected to 'style.css' to match login.php -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="brand-name">Anne's Salon</div>
            <div class="brand-tagline">Est. 2019 &mdash; Beauty &amp; Style</div>
        </div>

        <h2 class="auth-title">Create an Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- FIX: removed the stray wrapping <div class="form-group"> around the form -->
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                       placeholder="e.g. Maria Santos" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       placeholder="e.g. mariasantos" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                Create Account
            </button>
        </form>

        <div class="auth-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</div>
</body>
</html>
