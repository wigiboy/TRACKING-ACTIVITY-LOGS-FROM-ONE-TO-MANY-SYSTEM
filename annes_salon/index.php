<?php
// ============================================================
// index.php — Homepage / Dashboard
// ============================================================

session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once 'includes/db.php';
$page_title = 'Dashboard';

// Fetch quick stats
$stylist_count = $pdo->query("SELECT COUNT(*) FROM stylists")->fetchColumn();
$client_count  = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$log_count     = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$user_count    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Recent logs
$recent_logs = $pdo->query(
    "SELECT * FROM activity_logs ORDER BY performed_at DESC LIMIT 5"
)->fetchAll();

include 'includes/layout.php';
?>

<div class="home-hero">
    <h1>Welcome to <em>Anne's Salon</em></h1>
    <p>Management System &mdash; <?= htmlspecialchars($_SESSION['full_name']) ?></p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $stylist_count ?></div>
        <div class="stat-label">Stylists</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $client_count ?></div>
        <div class="stat-label">Clients</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $log_count ?></div>
        <div class="stat-label">Activity Logs</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $user_count ?></div>
        <div class="stat-label">System Users</div>
    </div>
</div>

<div class="quick-actions mb-2">
    <a href="stylists.php?action=add" class="btn btn-primary">Add Stylist</a>
    <a href="clients.php?action=add" class="btn btn-gold">Add Client</a>
    <a href="search.php" class="btn btn-secondary">Search Records</a>
    <a href="activity_logs.php" class="btn btn-secondary">View Activity Logs</a>
</div>

<?php if ($recent_logs): ?>
<div class="card">
    <div class="card-title">Recent Activity</div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_logs as $log): ?>
                <tr>
                    <td class="log-meta"><?= date('M j, Y g:i A', strtotime($log['performed_at'])) ?></td>
                    <td><strong><?= htmlspecialchars($log['username']) ?></strong></td>
                    <td>
                        <span class="badge badge-<?= strtolower($log['action']) ?>">
                            <?= htmlspecialchars($log['action']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($log['description']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="text-align:right; margin-top:1rem;">
        <a href="activity_logs.php" class="btn btn-secondary btn-sm">View All Logs</a>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>