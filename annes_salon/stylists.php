<?php
// ============================================================
// stylists.php — CRUD for Stylists
// ============================================================

session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once 'includes/db.php';
require_once 'includes/log_activity.php';

$action  = $_GET['action'] ?? 'list';
$id      = (int)($_GET['id'] ?? 0);
$error   = '';
$success = '';

// ---- DELETE ----
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT full_name FROM stylists WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare("DELETE FROM stylists WHERE id = :id")->execute([':id' => $id]);
        logActivity($pdo, 'DELETE', 'stylist', $id, "Deleted stylist: {$row['full_name']}");
    }
    header('Location: stylists.php?msg=deleted');
    exit;
}

// ---- POST: ADD or EDIT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name']  ?? '');
    $specialty  = trim($_POST['specialty']  ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $email      = trim($_POST['email']      ?? '');
    $hired_date = $_POST['hired_date']      ?? '';

    if (empty($full_name)) {
        $error = 'Full name is required.';
    } else {
        if ($action === 'add') {
            $stmt = $pdo->prepare(
                "INSERT INTO stylists (full_name, specialty, phone, email, hired_date)
                 VALUES (:full_name, :specialty, :phone, :email, :hired_date)"
            );
            $stmt->execute([
                ':full_name'  => $full_name,
                ':specialty'  => $specialty  ?: null,
                ':phone'      => $phone      ?: null,
                ':email'      => $email      ?: null,
                ':hired_date' => $hired_date ?: null,
            ]);
            $new_id = (int)$pdo->lastInsertId();
            logActivity($pdo, 'CREATE', 'stylist', $new_id, "Added new stylist: $full_name");
            header('Location: stylists.php?msg=added');
            exit;

        } elseif ($action === 'edit' && $id) {
            $stmt = $pdo->prepare(
                "UPDATE stylists
                 SET full_name=:full_name, specialty=:specialty,
                     phone=:phone, email=:email, hired_date=:hired_date
                 WHERE id=:id"
            );
            $stmt->execute([
                ':full_name'  => $full_name,
                ':specialty'  => $specialty  ?: null,
                ':phone'      => $phone      ?: null,
                ':email'      => $email      ?: null,
                ':hired_date' => $hired_date ?: null,
                ':id'         => $id,
            ]);
            logActivity($pdo, 'UPDATE', 'stylist', $id, "Updated stylist: $full_name");
            header('Location: stylists.php?msg=updated');
            exit;
        }
    }
}

// ---- Fetch record for EDIT ----
$stylist = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM stylists WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $stylist = $stmt->fetch();
    if (!$stylist) { header('Location: stylists.php'); exit; }
    // Log READ only on first load (GET)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        logActivity($pdo, 'READ', 'stylist', $id, "Viewed stylist record: {$stylist['full_name']}");
    }
}

// ---- Fetch all stylists for LIST ----
$stylists = [];
if ($action === 'list') {
    $stylists = $pdo->query("SELECT * FROM stylists ORDER BY full_name ASC")->fetchAll();
}

// ---- Flash messages ----
$msg_map = [
    'added'   => ['type' => 'success', 'text' => 'Stylist added successfully.'],
    'updated' => ['type' => 'success', 'text' => 'Stylist updated successfully.'],
    'deleted' => ['type' => 'success', 'text' => 'Stylist deleted.'],
];
$flash = isset($_GET['msg']) ? ($msg_map[$_GET['msg']] ?? null) : null;

$page_title = match($action) {
    'add'  => 'Add Stylist',
    'edit' => 'Edit Stylist',
    default => 'Stylists',
};

include 'includes/layout.php';
?>

<?php if ($action === 'list'): ?>

<div class="page-header">
    <h1 class="page-title"><span>Staff</span>Stylists</h1>
    <a href="stylists.php?action=add" class="btn btn-primary">+ Add Stylist</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['text']) ?></div>
<?php endif; ?>

<?php if (empty($stylists)): ?>
    <div class="empty-state"><p>No stylists on record yet.</p>Add your first stylist to get started.</div>
<?php else: ?>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Specialty</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Hired</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stylists as $s): ?>
            <tr>
                <td class="log-meta"><?= $s['id'] ?></td>
                <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                <td><?= htmlspecialchars($s['specialty'] ?? '—') ?></td>
                <td><?= htmlspecialchars($s['phone'] ?? '—') ?></td>
                <td><?= htmlspecialchars($s['email'] ?? '—') ?></td>
                <td class="log-meta"><?= $s['hired_date'] ? date('M j, Y', strtotime($s['hired_date'])) : '—' ?></td>
                <td>
                    <div class="table-actions">
                        <a href="stylists.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-gold btn-sm">Edit</a>
                        <a href="stylists.php?action=delete&id=<?= $s['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete <?= htmlspecialchars(addslashes($s['full_name'])) ?>? This will also remove their clients.')">
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
        <span>Staff</span><?= $action === 'add' ? 'Add New Stylist' : 'Edit Stylist' ?>
    </h1>
    <a href="stylists.php" class="btn btn-secondary">← Back to Stylists</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title"><?= $action === 'add' ? 'Stylist Details' : 'Update Details' ?></div>
    <form method="POST" action="stylists.php?action=<?= $action ?><?= $id ? '&id='.$id : '' ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="full_name">Full Name <span style="color:var(--red)">*</span></label>
                <input type="text" id="full_name" name="full_name" required
                       value="<?= htmlspecialchars($_POST['full_name'] ?? $stylist['full_name'] ?? '') ?>"
                       placeholder="e.g. Maria Santos">
            </div>
            <div class="form-group">
                <label for="specialty">Specialty</label>
                <input type="text" id="specialty" name="specialty"
                       value="<?= htmlspecialchars($_POST['specialty'] ?? $stylist['specialty'] ?? '') ?>"
                       placeholder="e.g. Hair Coloring">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone"
                       value="<?= htmlspecialchars($_POST['phone'] ?? $stylist['phone'] ?? '') ?>"
                       placeholder="e.g. 09171234567">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? $stylist['email'] ?? '') ?>"
                       placeholder="e.g. maria@example.com">
            </div>
        </div>
        <div class="form-group" style="max-width:280px">
            <label for="hired_date">Hired Date</label>
            <input type="date" id="hired_date" name="hired_date"
                   value="<?= htmlspecialchars($_POST['hired_date'] ?? $stylist['hired_date'] ?? '') ?>">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $action === 'add' ? 'Add Stylist' : 'Save Changes' ?>
            </button>
            <a href="stylists.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
