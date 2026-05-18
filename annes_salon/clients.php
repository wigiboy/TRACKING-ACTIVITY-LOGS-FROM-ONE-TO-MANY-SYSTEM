<?php
// ============================================================
// clients.php — CRUD for Clients
// ============================================================

session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once 'includes/db.php';
require_once 'includes/log_activity.php';

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$error  = '';

// ---- DELETE ----
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT full_name FROM clients WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare("DELETE FROM clients WHERE id = :id")->execute([':id' => $id]);
        logActivity($pdo, 'DELETE', 'client', $id, "Deleted client: {$row['full_name']}");
    }
    header('Location: clients.php?msg=deleted');
    exit;
}

// ---- POST: ADD or EDIT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stylist_id = (int)($_POST['stylist_id'] ?? 0);
    $full_name  = trim($_POST['full_name']  ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $email      = trim($_POST['email']      ?? '');
    $notes      = trim($_POST['notes']      ?? '');

    if (empty($full_name)) {
        $error = 'Full name is required.';
    } elseif (!$stylist_id) {
        $error = 'Please select an assigned stylist.';
    } else {
        if ($action === 'add') {
            $stmt = $pdo->prepare(
                "INSERT INTO clients (stylist_id, full_name, phone, email, notes)
                 VALUES (:stylist_id, :full_name, :phone, :email, :notes)"
            );
            $stmt->execute([
                ':stylist_id' => $stylist_id,
                ':full_name'  => $full_name,
                ':phone'      => $phone ?: null,
                ':email'      => $email ?: null,
                ':notes'      => $notes ?: null,
            ]);
            $new_id = (int)$pdo->lastInsertId();
            logActivity($pdo, 'CREATE', 'client', $new_id, "Added new client: $full_name");
            header('Location: clients.php?msg=added');
            exit;

        } elseif ($action === 'edit' && $id) {
            $stmt = $pdo->prepare(
                "UPDATE clients
                 SET stylist_id=:stylist_id, full_name=:full_name,
                     phone=:phone, email=:email, notes=:notes
                 WHERE id=:id"
            );
            $stmt->execute([
                ':stylist_id' => $stylist_id,
                ':full_name'  => $full_name,
                ':phone'      => $phone ?: null,
                ':email'      => $email ?: null,
                ':notes'      => $notes ?: null,
                ':id'         => $id,
            ]);
            logActivity($pdo, 'UPDATE', 'client', $id, "Updated client: $full_name");
            header('Location: clients.php?msg=updated');
            exit;
        }
    }
}

// ---- Fetch record for EDIT ----
$client = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $client = $stmt->fetch();
    if (!$client) { header('Location: clients.php'); exit; }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        logActivity($pdo, 'READ', 'client', $id, "Viewed client record: {$client['full_name']}");
    }
}

// ---- All stylists (for dropdown) ----
$stylists = $pdo->query("SELECT id, full_name FROM stylists ORDER BY full_name ASC")->fetchAll();

// ---- Filter by stylist (list view) ----
$filter_stylist = (int)($_GET['stylist_id'] ?? 0);

// ---- Fetch all clients for LIST ----
$clients = [];
if ($action === 'list') {
    if ($filter_stylist) {
        $stmt = $pdo->prepare(
            "SELECT c.*, s.full_name AS stylist_name
             FROM clients c
             JOIN stylists s ON c.stylist_id = s.id
             WHERE c.stylist_id = :sid
             ORDER BY c.full_name ASC"
        );
        $stmt->execute([':sid' => $filter_stylist]);
    } else {
        $stmt = $pdo->query(
            "SELECT c.*, s.full_name AS stylist_name
             FROM clients c
             JOIN stylists s ON c.stylist_id = s.id
             ORDER BY c.full_name ASC"
        );
    }
    $clients = $stmt->fetchAll();
}

// ---- Flash messages ----
$msg_map = [
    'added'   => ['type' => 'success', 'text' => 'Client added successfully.'],
    'updated' => ['type' => 'success', 'text' => 'Client updated successfully.'],
    'deleted' => ['type' => 'success', 'text' => 'Client deleted.'],
];
$flash = isset($_GET['msg']) ? ($msg_map[$_GET['msg']] ?? null) : null;

$page_title = match($action) {
    'add'  => 'Add Client',
    'edit' => 'Edit Client',
    default => 'Clients',
};

include 'includes/layout.php';
?>

<?php if ($action === 'list'): ?>

<div class="page-header">
    <h1 class="page-title"><span>Records</span>Clients</h1>
    <a href="clients.php?action=add" class="btn btn-gold">+ Add Client</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['text']) ?></div>
<?php endif; ?>

<?php if ($stylists): ?>
<div class="filter-bar">
    <label for="filter_stylist">Filter by Stylist:</label>
    <select id="filter_stylist" onchange="location.href='clients.php?stylist_id='+this.value">
        <option value="0">— All Stylists —</option>
        <?php foreach ($stylists as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $filter_stylist === (int)$s['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['full_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<?php if (empty($clients)): ?>
    <div class="empty-state"><p>No clients found.</p>
        <?= $filter_stylist ? 'No clients are assigned to this stylist.' : 'Add your first client to get started.' ?>
    </div>
<?php else: ?>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Assigned Stylist</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
                <td class="log-meta"><?= $c['id'] ?></td>
                <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                <td>
                    <span class="badge badge-plum"><?= htmlspecialchars($c['stylist_name']) ?></span>
                </td>
                <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                <td class="text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?= htmlspecialchars($c['notes'] ?? '—') ?>
                </td>
                <td>
                    <div class="table-actions">
                        <a href="clients.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-gold btn-sm">Edit</a>
                        <a href="clients.php?action=delete&id=<?= $c['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete client <?= htmlspecialchars(addslashes($c['full_name'])) ?>?')">
                           Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

<div class="page-header">
    <h1 class="page-title">
        <span>Records</span><?= $action === 'add' ? 'Add New Client' : 'Edit Client' ?>
    </h1>
    <a href="clients.php" class="btn btn-secondary">← Back to Clients</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($stylists)): ?>
    <div class="alert alert-info">
        You must <a href="stylists.php?action=add">add a stylist</a> before adding clients.
    </div>
<?php else: ?>
<div class="card">
    <div class="card-title"><?= $action === 'add' ? 'Client Details' : 'Update Details' ?></div>
    <form method="POST" action="clients.php?action=<?= $action ?><?= $id ? '&id='.$id : '' ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="full_name">Full Name <span style="color:var(--red)">*</span></label>
                <input type="text" id="full_name" name="full_name" required
                       value="<?= htmlspecialchars($_POST['full_name'] ?? $client['full_name'] ?? '') ?>"
                       placeholder="e.g. Ana Reyes">
            </div>
            <div class="form-group">
                <label for="stylist_id">Assigned Stylist <span style="color:var(--red)">*</span></label>
                <select id="stylist_id" name="stylist_id" required>
                    <option value="">— Select Stylist —</option>
                    <?php foreach ($stylists as $s): ?>
                        <?php $sel = ((int)($_POST['stylist_id'] ?? $client['stylist_id'] ?? 0)) === (int)$s['id']; ?>
                        <option value="<?= $s['id'] ?>" <?= $sel ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone"
                       value="<?= htmlspecialchars($_POST['phone'] ?? $client['phone'] ?? '') ?>"
                       placeholder="e.g. 09171234567">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? $client['email'] ?? '') ?>"
                       placeholder="e.g. ana@example.com">
            </div>
        </div>
        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"
                      placeholder="e.g. Prefers highlights, allergic to certain dyes…"><?= htmlspecialchars($_POST['notes'] ?? $client['notes'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $action === 'add' ? 'Add Client' : 'Save Changes' ?>
            </button>
            <a href="clients.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
