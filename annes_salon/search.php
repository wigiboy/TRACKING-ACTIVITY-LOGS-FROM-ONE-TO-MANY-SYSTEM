<?php
// ============================================================
// search.php — Search Stylists & Clients
// ============================================================

session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once 'includes/db.php';
require_once 'includes/log_activity.php';

$query    = trim($_GET['q'] ?? '');
$type     = $_GET['type'] ?? 'all';
$stylists = [];
$clients  = [];
$searched = false;

if ($query !== '') {
    $searched = true;
    $like = '%' . $query . '%';

    if ($type === 'all' || $type === 'stylist') {
        $stmt = $pdo->prepare(
            "SELECT * FROM stylists
             WHERE full_name LIKE :q1 OR specialty LIKE :q2 OR phone LIKE :q3 OR email LIKE :q4
             ORDER BY full_name ASC"
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like]);
        $stylists = $stmt->fetchAll();
    }

    if ($type === 'all' || $type === 'client') {
        $stmt = $pdo->prepare(
            "SELECT c.*, s.full_name AS stylist_name
             FROM clients c
             JOIN stylists s ON c.stylist_id = s.id
             WHERE c.full_name LIKE :q1 OR c.phone LIKE :q2 OR c.email LIKE :q3 OR c.notes LIKE :q4
             ORDER BY c.full_name ASC"
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like]);
        $clients = $stmt->fetchAll();
    }

    // Log the search
    logActivity($pdo, 'READ', 'search', 0, "Searched for \"$query\" (type: $type)");
}

$page_title = 'Search';
include 'includes/layout.php';
?>

<div class="page-header">
    <h1 class="page-title"><span>System</span>Search Records</h1>
</div>

<form method="GET" action="search.php">
<div class="search-form">
    <div class="form-group">
        <label for="q">Search keyword</label>
        <input type="text" id="q" name="q"
               value="<?= htmlspecialchars($query) ?>"
               placeholder="Name, specialty, phone, email…" autofocus>
    </div>
    <div class="form-group" style="max-width:180px">
        <label for="type">Search in</label>
        <select id="type" name="type">
            <option value="all"     <?= $type === 'all'     ? 'selected' : '' ?>>All Records</option>
            <option value="stylist" <?= $type === 'stylist' ? 'selected' : '' ?>>Stylists Only</option>
            <option value="client"  <?= $type === 'client'  ? 'selected' : '' ?>>Clients Only</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
</div>
</form>

<?php if ($searched): ?>

    <?php $total = count($stylists) + count($clients); ?>
    <?php if ($total === 0): ?>
        <div class="empty-state">
            <p>No results found.</p>
            No records matched &ldquo;<?= htmlspecialchars($query) ?>&rdquo;.
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Found <strong><?= $total ?></strong> result<?= $total !== 1 ? 's' : '' ?>
            for &ldquo;<?= htmlspecialchars($query) ?>&rdquo;.
        </div>
    <?php endif; ?>

    <?php if ($stylists): ?>
    <div class="results-section">
        <h3>Stylists (<?= count($stylists) ?>)</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Specialty</th>
                        <th>Phone</th>
                        <th>Email</th>
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
                        <td>
                            <a href="stylists.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-gold btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($clients): ?>
    <div class="results-section">
        <h3>Clients (<?= count($clients) ?>)</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Stylist</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                    <tr>
                        <td class="log-meta"><?= $c['id'] ?></td>
                        <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                        <td><span class="badge badge-plum"><?= htmlspecialchars($c['stylist_name']) ?></span></td>
                        <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                        <td>
                            <a href="clients.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-gold btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

<?php else: ?>
    <div class="empty-state">
        <p>Enter a keyword above to search.</p>
        Search across stylist and client records by name, specialty, phone, or email.
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
