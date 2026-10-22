<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];

if (!in_array($role, ['customer', 'student'], true)) {
    http_response_code(403);
    exit('Access denied.');
}

if ($role == 'student') {
    $link = '../student/orders.php';
    $ttl = 'Back to Orders';
} else {
    $link = '../customer/dashboard.php';
    $ttl = 'Back to Dashboard';
}

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
}

if (!$orderId) {
    exit('Invalid order ID.');
}

// Confirm this user belongs to the order.
$stmt = $pdo->prepare("
    SELECT id, customer_id, student_id, status
    FROM orders
    WHERE id = ?
      AND (customer_id = ? OR student_id = ?)
");

$stmt->execute([$orderId, $userId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    exit('Order not found.');
}

// Verify that the session role matches the order participant.
if (($role === 'customer' && (int) $order['customer_id'] !== $userId) ||
    ($role === 'student' && (int) $order['student_id'] !== $userId)) {
    http_response_code(403);
    exit('Access denied.');
}

$categories = ($role === 'customer')
    ? [
        'delayed_delivery' => 'Delayed delivery',
        'quality_concern' => 'Quality concern',
        'revision_dispute' => 'Work or revisions dispute',
        'other' => 'Other issue'
    ]
    : [
        'scope_creep' => 'Extra work outside the agreed brief',
        'revision_dispute' => 'Excessive or disputed revisions',
        'payment_delay' => 'Payment delay',
        'other' => 'Other issue'
    ];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $issueType = $_POST['issue_type'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!isset($categories[$issueType])) {
        $error = 'Please select a valid issue type.';
    } elseif ($subject === '' || $description === '') {
        $error = 'Subject and description are required.';
    } elseif (
        mb_strlen($subject) > 150 ||
        mb_strlen($description) > 5000
    ) {
        $error = 'The subject or description is too long.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO support_tickets (
                order_id,
                reporter_id,
                reporter_role,
                issue_type,
                subject,
                description
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$orderId, $userId, $role,
                        $issueType, $subject, $description
        ]);

        redirect('my-tickets.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <title>Report an Issue - DiskarDesk</title>
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-5" style="max-width: 760px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= $link ?>" class="btn btn-secondary">
            <?= $ttl ?>
        </a>
    </div>

    <h1 class="h3 mb-2">Report an Issue</h1>
    <p class="text-muted">Order #<?= (int) $order['id'] ?></p>

    <div class="alert alert-info">
        Describe what happened and include relevant details.
        Please do not include passwords or sensitive account information.
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

        <div class="mb-3">
            <label for="issue_type" class="form-label">
                Issue type
            </label>

            <select name="issue_type" id="issue_type" class="form-select" required>
                <option value="">Select an issue</option>

                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= e($key) ?>"
                        <?= (($_POST['issue_type'] ?? '') === $key) ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" name="subject" id="subject" maxlength="150" class="form-control" required
                value="<?= e($_POST['subject'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">
                Explain the issue
            </label>
            <textarea name="description" id="description" class="form-control" rows="6"
                maxlength="5000" required><?= e($_POST['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Report</button>

        <a href="my-tickets.php" class="btn btn-outline-secondary"> My Support Cases</a>
    </form>

</div>
</body>
</html>
