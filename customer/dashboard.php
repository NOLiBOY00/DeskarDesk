<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('customer');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT name, email FROM users
    WHERE id = ?
");

$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$orderStmt = $pdo->prepare("
    SELECT o.id, o.status, o.price,
        o.request_message, o.delivery_message, o.delivered_at, 
        o.created_at, s.title, u.name AS student_name,
        r.id AS review_id
    FROM orders o
    INNER JOIN services s
        ON s.id = o.service_id
    INNER JOIN users u
        ON u.id = o.student_id
    LEFT JOIN reviews r
        ON r.order_id = o.id
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
");

$orderStmt->execute([$userId]);
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - DiskarDesk</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand">
            <img src="../public/logo/logo_only.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk
        </a>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="marketplace.php" class="btn btn-outline-light btn-sm">Marketplace</a>
            <a href="orders.php" class="btn btn-outline-light btn-sm">Orders</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>


<div class="container mt-5">

    <div class="card shadow">
        <div class="card-body">
            <h2>Welcome, <?= e($user['name']) ?>!</h2>
            <p class="text-muted">Browse services offered by students.</p>
            <a href="marketplace.php" class="btn btn-primary">Browse Marketplace</a>
        </div>
    </div>

    <div class="card shadow mt-4">

        <div class="card-body">

            <h4 class="mb-3">My Requests</h4>


            <?php if (count($orders) === 0): ?>
                <p class="text-muted">You have not requested any services yet.</p>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Student</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($orders as $order): ?>

                                <tr>
                                    <td><?= e($order['title']) ?></td>
                                    <td><?= e($order['student_name']) ?></td>
                                    <td>₱<?= number_format($order['price'],2) ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match ($order['status']) {
                                            'pending' => 'bg-warning text-dark',
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'denied' => 'bg-danger',
                                            'in_progress' => 'bg-primary',
                                            'delivered' => 'bg-info text-dark',
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= e($order['status']) ?></span>
                                    </td>
                                    <td><?= e($order['created_at']) ?></td>
                                    <td>
                                        <?php if ($order['status'] === 'completed'
                                            && empty($order['review_id'])): 
                                        ?>
                                            <a href="review-order.php?id=<?= (int) $order['id'] ?>" class="btn btn-primary">
                                                Leave Review
                                            </a>
                                        <?php elseif ($order['status'] === 'completed'
                                            && !empty($order['review_id'])): 
                                        ?>
                                            <span class="badge bg-success">Reviewed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../support/report.php?order_id=<?= (int) $order['id'] ?>"class="btn btn-outline-danger btn-sm">
                                            Report an Issue
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>


</body>
</html>