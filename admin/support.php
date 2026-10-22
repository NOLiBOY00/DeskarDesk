<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$allowedStatuses = [
    'open', 'under_review', 'awaiting_response',
    'resolved', 'closed'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);

    $status = $_POST['status'] ?? '';
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    $resolution = trim($_POST['resolution'] ?? '');

    if (!$ticketId || !in_array($status, $allowedStatuses, true) ||
        mb_strlen($adminNotes) > 5000 || mb_strlen($resolution) > 5000) {
        exit('Invalid support-case update.');
    }

    $stmt = $pdo->prepare("
        UPDATE support_tickets
        SET
            status = ?,
            admin_notes = ?,
            resolution = ?,
            resolved_at = CASE
                WHEN ? IN ('resolved', 'closed')
                    THEN COALESCE(resolved_at, NOW())
                ELSE NULL
            END
        WHERE id = ?
    ");

    $stmt->execute([
        $status, $adminNotes, $resolution,
        $status, $ticketId
    ]);

    redirect('support.php');
}

$stmt = $pdo->query("
    SELECT t.*,
        reporter.name AS reporter_name,
        reporter.email AS reporter_email
    FROM support_tickets t
    INNER JOIN users reporter
        ON reporter.id = t.reporter_id
    ORDER BY
        CASE
            WHEN t.status IN ('open', 'under_review',
                              'awaiting_response') THEN 0
            ELSE 1
        END,
        t.created_at DESC
");

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <title>Support Management - DISKARDESK</title>
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-5">

    <h1 class="h3 mb-4">Disputes & Support Management</h1>

    <?php if (!$tickets): ?>
        <div class="alert alert-info">
            No support reports have been submitted.
        </div>
    <?php endif; ?>

    <?php foreach ($tickets as $ticket): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h2 class="h5">
                    Case #<?= (int) $ticket['id'] ?>:
                    <?= e($ticket['subject']) ?>
                </h2>

                <p class="text-muted small">
                    Order #<?= (int) $ticket['order_id'] ?>
                    · Reporter: <?= e($ticket['reporter_name']) ?>
                    (<?= e($ticket['reporter_role']) ?>)
                    · <?= e($ticket['reporter_email']) ?>
                </p>

                <p>
                    <strong>Issue:</strong>
                    <?= e(str_replace('_', ' ', $ticket['issue_type'])) ?>
                </p>

                <p><?= nl2br(e($ticket['description'])) ?></p>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="ticket_id" value="<?= (int) $ticket['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Case status</label>

                        <select name="status" class="form-select" required>
                            <?php foreach ($allowedStatuses as $status): ?>
                                <option value="<?= e($status) ?>" <?= $ticket['status'] === $status ? 'selected' : '' ?>>
                                    <?= e(ucwords(str_replace('_', ' ', $status))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Internal admin notes</label>

                        <textarea name="admin_notes" class="form-control" rows="2"
                            maxlength="5000"><?= e($ticket['admin_notes'] ?? '') ?></textarea>

                        <div class="form-text">
                            Internal notes are for admins and should not
                            contain information intended for the user.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Resolution visible to the reporter
                        </label>

                        <textarea name="resolution" class="form-control" rows="3"
                            maxlength="5000"><?= e($ticket['resolution'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Case</button>
                </form>

            </div>
        </div>
    <?php endforeach; ?>

    <a href="dashboard.php" class="btn btn-outline-secondary">
        Back to Admin Dashboard
    </a>

</div>
</body>
</html>
