<?php
// ============================================================
// includes/log_activity.php
// Helper function to insert a record into activity_logs
// Called after every CREATE, READ, UPDATE, DELETE operation
// ============================================================

/**
 * Log a database operation to the activity_logs table.
 *
 * @param PDO    $pdo         The database connection
 * @param string $action      CREATE | UPDATE | DELETE | READ
 * @param string $entity_type stylist | client
 * @param int    $entity_id   The ID of the record acted upon
 * @param string $description Human-readable description of the action
 */
function logActivity(PDO $pdo, string $action, string $entity_type, int $entity_id, string $description): void
{
    // FIX: session_start() must be called before accessing $_SESSION.
    // Use session_status() check so we don't call it if a session is already active
    // (e.g. when the calling page already started it).
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Only log if a user is logged in
    $username = $_SESSION['username'] ?? 'system';

    $stmt = $pdo->prepare(
        "INSERT INTO activity_logs (username, action, entity_type, entity_id, description)
         VALUES (:username, :action, :entity_type, :entity_id, :description)"
    );
    $stmt->execute([
        ':username'    => $username,
        ':action'      => $action,
        ':entity_type' => $entity_type,
        ':entity_id'   => $entity_id,
        ':description' => $description,
    ]);
}
?>
