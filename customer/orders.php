<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('customer');

$customerId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get customer's orders
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT o.id, o.status, o.delivery_message,
        o.delivered_at, o.completed_at, o.created_at, s.title AS service_name
    FROM orders o
    INNER JOIN services s
        ON o.service_id = s.id
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
");

$stmt->execute([$customerId]);
$orders = $stmt->fetchAll();



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Orders - DiskarDesk</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Orders</h2>
            <p class="text-muted mb-0">View and manage your service orders.</p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
    </div>


    <?php if (empty($orders)): ?>
        <div class="alert alert-info">You don't have any orders yet.</div>
    <?php else: ?>

        <?php foreach ($orders as $order): ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    <!-- SERVICE -->
                    <h5 class="card-title">Service: <?= e($order['service_name']) ?></h5>

                    <!-- ORDER ID -->
                    <p class="text-muted mb-2">Order #<?= e($order['id']) ?></p>

                    <!-- STATUS -->
                    <p>
                        <strong>Status:</strong>
                        <?php $status = strtoupper($order['status']); ?>

                        <?php if ($order['status'] === 'pending'): ?>
                            <span class="badge bg-warning text-dark"><?= e($status) ?></span>
                        <?php elseif ($order['status'] === 'approved'): ?>
                            <span class="badge bg-primary"><?= e($status) ?></span>
                        <?php elseif ($order['status'] === 'in_progress'): ?>
                            <span class="badge bg-info text-dark"><?= e($status) ?></span>
                        <?php elseif ($order['status'] === 'delivered'): ?>
                            <span class="badge bg-success"><?= e($status) ?></span>
                        <?php elseif ($order['status'] === 'completed'): ?>
                            <span class="badge bg-dark"><?= e($status) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= e($status) ?></span>
                        <?php endif; ?>

                    </p>


                    <!-- DELIVERY MESSAGE -->
                    <?php if (!empty($order['delivery_message'])): ?>
                        <div class="alert alert-success mt-3">
                            <strong>Student Delivery:</strong>
                            <p class="mb-0 mt-2"><?= nl2br(e($order['delivery_message'])) ?></p>

                            <?php if (!empty($order['delivered_at'])): ?>
                                <small class="text-muted">Delivered:<?= e($order['delivered_at']) ?></small>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>

                    <!-- COMPLETE ORDER -->
                    <?php if ($order['status'] === 'delivered'): ?>

                        <form method="POST" action="complete-order.php" class="mt-3">
                            <input type="hidden" name="csrf_token" id="" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="order_id" value="<?= e($order['id']) ?>">
                            <button type="submit" class="btn btn-primary">Mark Order as Completed</button>
                        </form>
                    <?php endif; ?>


                    <!-- COMPLETED INFORMATION -->
                    <?php if ($order['status'] === 'completed'): ?>

                        <div class="alert alert-dark mt-3">
                            <strong>Order Completed</strong>

                            <?php if (!empty($order['completed_at'])): ?>
                                <br>
                                <small>Completed: <?= e($order['completed_at']) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>


                    <!-- CREATED DATE -->
                    <small class="text-muted d-block mt-3">Order created: <?= e($order['created_at']) ?></small>

                    <a href="messages.php?order_id=<?= (int) $order['id'] ?>" class="btn btn-outline-primary">Messages</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>


</div>

</body>
</html>
