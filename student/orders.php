<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$studentId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get incoming orders
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT o.id, o.price, o.status,
        o.request_message, o.delivery_message, o.delivered_at, 
        o.completed_at, o.created_at, s.title AS service_title,
        u.name AS customer_name, u.email AS customer_email,
        r.rating, r.comment AS review_comment, r.created_at AS review_created_at
    FROM orders o
    INNER JOIN services s
        ON s.id = o.service_id
    INNER JOIN users u
        ON u.id = o.customer_id
    LEFT JOIN reviews r
        ON r.order_id = o.id
    WHERE o.student_id = ?
    ORDER BY o.created_at DESC
");

$stmt->execute([$studentId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - DiskarDesk</title>
    <link rel="icon" href="../public/logo/Logo_trns.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css"rel="stylesheet">
</head>

<body>


<nav class="navbar navbar-dark bg-dark">

    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../public/logo/Logo_trns.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk 
        </a>        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="services.php" class="btn btn-outline-light btn-sm">My Services</a>
            <a href="orders.php" class="btn btn-light btn-sm">Orders</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>


<div class="container mt-5">

    <div class="mb-4">
        <h1>Service Requests</h1>
        <p class="text-muted">Manage requests from customers.</p>
    </div>


    <?php if (count($orders) === 0): ?>
        <div class="alert alert-info">
            You don't have any service requests yet.
        </div>
    <?php else: ?>


        <?php foreach ($orders as $order): ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">

                        <!-- Order Information -->
                        <div class="col-md-8">
                            <h4><?= e($order['service_title']) ?></h4>
                            <p class="mb-1"><strong>Customer:</strong> <?= e($order['customer_name']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= e($order['customer_email']) ?></p>
                            <p class="mb-1"><strong>Price:</strong> ₱<?= number_format($order['price'],2) ?></p>
                            <p class="mb-3"><strong>Requested:</strong> <?= e($order['created_at']) ?></p>

                            <div>
                                <strong>Customer Message:</strong>
                                <p class="mt-2">
                                    <?php if (!empty($order['request_message'])): ?>
                                        <?= nl2br(e($order['request_message'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">No message provided.</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Status / Actions -->
                        <div class="col-md-4">

                            <!-- Customer review-->
                            <?php if (!empty($order['rating'])): ?>
                                <div class="alert alert-info mt-3">
                                    <strong>Customer Review</strong>

                                    <div class="mt-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $order['rating']): ?>
                                                ⭐
                                            <?php else: ?>
                                                ☆
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>

                                    <?php if (!empty($order['review_comment'])): ?>
                                        <p class="mt-2 mb-0"><?= nl2br(e($order['review_comment'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">

                                <!-- order information-->
                                <?php if (!empty($order['delivery_message'])): ?>

                                    <div class="alert alert-success mt-3">
                                        <strong>Student Delivery:</strong>
                                        <p class="mb-0 mt-2"><?= nl2br(e($order['delivery_message'])) ?></p>
                                        <?php if (!empty($order['delivered_at'])): ?>
                                            <small class="text-muted">Delivered: <?= e($order['delivered_at']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $badgeClass = match ($order['status']) {
                                    'pending' => 'bg-warning text-dark',
                                    'approved' => 'bg-success',
                                    'denied' => 'bg-danger',
                                    'in_progress' =>'bg-primary',
                                    'delivered' => 'bg-info text-dark',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> fs-6"><?= e($order['status']) ?></span>
                            </div>

                            <?php if ($order['status'] === 'approved'): ?>
                                <form method="POST" action="start-order.php" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                    <button type="submit" class="btn btn-primary">Start Work</button>
                                </form>
                            <?php elseif ($order['status'] === 'in_progress'): ?>
                                <a href="deliver-order.php?id=<?= (int) $order['id'] ?>" class="btn btn-success">Deliver Work</a>
                            <?php elseif ($order['status'] === 'delivered'): ?>
                                <span class="text-success">Waiting for customer to complete the order.</span>
                            <?php elseif ($order['status'] === 'completed'): ?>
                                <span class="text-success">Order Completed</span>
                            <?php endif; ?>

                            <?php if ($order['status'] === 'pending'): ?>
                                <div class="d-grid gap-2">
                                    <form method="POST" action="approve-order.php">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    <form method="POST"action="deny-order.php">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                        <button type="submit"class="btn btn-danger">Deny</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            <!-- messages -->
                            <a href="messages.php?order_id=<?= (int) $order['id'] ?>" class="btn btn-outline-primary">
                                Messages
                            </a>
                            <a href="../support/report.php?order_id=<?= (int) $order['id'] ?>" class="btn btn-outline-danger btn-sm">
                                Report an Issue
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>


</body>
</html>