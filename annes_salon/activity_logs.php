<?php
// ============================================================
// activity_logs.php — View Activity Logs
// ============================================================

session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once 'includes/db.php';

// ---- Filters ----
$filter_action = $_GET['action']   ?? '';
$filter_entity = $_GET['entity']   ?? '';
$filter_user   = trim($_GET['user'] ?? '');

// Build WHERE clause
$where  = [];
$params = [];

if ($filter_action && in_array($filter_action, ['CREATE','READ','UPDATE','DELETE'])) {
    $where[]          = "action = :action";
    $params[':action'] = $filter_action;
}
if ($filter_entity && in_array($filter_entity, ['stylist','client','search'])) {
    $where[]           = "entity_type = :entity";
    $params[':entity']  = $filter_entity;
}
if ($filter_user !== '') {
    $where[]          = "username LIKE :user";
    $params[':user']   = '%' . $filter_user . '%';
}

$sql = "SELECT * FROM activity_logs";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY performed_at DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// ---- Distinct users for filter dropdown ----
$users = $pdo->query("SELECT DISTINCT username FROM activity_logs ORDER BY username ASC")->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Activity Logs';
include 'includes/layout.php';
?>

<div class="page-header">
    <h1 class="page-title"><span>System</span>Activity Logs</h1>
    <span class="text-muted"><?= count($logs) ?> record<?= count($logs) !== 1 ? 's' : '' ?></span>
</div>

<!-- Filter Bar -->
<form method="GET" action="activity_logs.php">
    <div class="filter-bar">
        <label>Action:</label>
        <select name="action">
            <option value="">All Actions</option>
            <?php foreach (['CREATE','READ','UPDATE','DELETE'] as $a): ?>
                <option value="<?= $a ?>" <?= $filter_action === $a ? 'selected' : '' ?>><?= $a ?></option>
            <?php endforeach; ?>
        </select>

        <label>Entity:</label>
        <select name="entity">
            <option value="">All Entities</option>
            <?php foreach (['stylist','client','search'] as $e): ?>
                <option value="<?= $e ?>" <?= $filter_entity === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
            <?php endforeach; ?>
        </select>

        <label>User:</label>
        <select name="user">
            <option value="">All Users</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= htmlspecialchars($u) ?>" <?= $filter_user === $u ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="activity_logs.php" class="btn btn-secondary btn-sm">Clear</a>
    </div>
</form>

<?php if (empty($logs)): ?>
    <div class="empty-state"><p>No logs found.</p>No activity matches your current filters.</div>
<?php else: ?>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Entity</th>
                <th>ID</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td class="log-meta" style="white-space:nowrap">
                    <?= date('M j, Y', strtotime($log['performed_at'])) ?><br>
                    <span style="color:var(--muted)"><?= date('g:i A', strtotime($log['performed_at'])) ?></span>
                </td>
                <td><strong><?= htmlspecialchars($log['username']) ?></strong></td>
                <td>
                    <span class="badge badge-<?= strtolower($log['action']) ?>">
                        <?= htmlspecialchars($log['action']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge-<?= $log['entity_type'] === 'stylist' ? 'plum' : ($log['entity_type'] === 'client' ? 'gold' : 'read') ?>">
                        <?= htmlspecialchars($log['entity_type']) ?>
                    </span>
                </td>
                <td class="log-meta"><?= $log['entity_id'] ?: '—' ?></td>
                <td><?= htmlspecialchars($log['description']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
