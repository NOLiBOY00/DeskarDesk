<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

if (!in_array($_SESSION['role'], ['customer', 'student'], true)) {
    http_response_code(403);
    exit('Access denied.');
}

$stmt = $pdo->prepare("
    SELECT id, order_id, issue_type, subject,
        description, status, resolution,
        created_at, updated_at, resolved_at
    FROM support_tickets
    WHERE reporter_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$userId]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Support Cases - DsikarDesk</title>
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-5">

    <h1 class="h3 mb-4">My Support Cases</h1>

    <?php if (!$tickets): ?>
        <div class="alert alert-info">
            You have not submitted any support reports yet.
        </div>
    <?php else: ?>

        <?php foreach ($tickets as $ticket): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-body">

                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <h2 class="h5"><?= e($ticket['subject']) ?></h2>

                        <span class="badge text-bg-secondary">
                            <?= e(strtoupper($ticket['status'])) ?>
                        </span>
                    </div>

                    <p class="text-muted small mb-2">
                        Case #<?= (int) $ticket['id'] ?>
                        · Order #<?= (int) $ticket['order_id'] ?>
                        · <?= e($ticket['created_at']) ?>
                    </p>

                    <p>
                        <strong>Issue:</strong>
                        <?= e(str_replace('_', ' ', $ticket['issue_type'])) ?>
                    </p>

                    <p><?= nl2br(e($ticket['description'])) ?></p>

                    <?php if (!empty($ticket['resolution'])): ?>
                        <hr>
                        <h3 class="h6">Admin Resolution</h3>
                        <p><?= nl2br(e($ticket['resolution'])) ?></p>
                    <?php endif; ?>

                    <p class="text-muted small mb-0">
                        Last updated: <?= e($ticket['updated_at']) ?>
                    </p>

                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <a href="../index.php" class="btn btn-outline-secondary">
        Back to Home
    </a>

</div>
</body>
</html>
