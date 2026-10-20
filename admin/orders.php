<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$stmt = $pdo->query("
    SELECT o.id, o.price, o.status,
        o.created_at, s.title AS service_title,
        student.name AS student_name, customer.name AS customer_name
    FROM orders o
    INNER JOIN services s
        ON s.id = o.service_id
    INNER JOIN users student
        ON student.id = o.student_id
    INNER JOIN users customer
        ON customer.id = o.customer_id
    ORDER BY o.created_at DESC
");

$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders - DiskarDesk Admin</title>
    <link rel="icon" href="../public/logo/Logo_trns.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand"><img src="../public/logo/Logo_trns.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
               Orders
            </a>
        <div>
            <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
            <a href="users.php" class="btn btn-primary">Users</a>
            <a href="services.php" class="btn btn-success">Services</a>
            <a href="orders.php" class="btn btn-warning">Orders</a>
        </div>
        </div>
    </nav>

<div class="container py-5">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service</th>
                            <th>Student</th>
                            <th>Customer</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= (int) $order['id'] ?></td>
                            <td><?= e($order['service_title']) ?></td>
                            <td><?= e($order['student_name']) ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td>₱<?= number_format($order['price'], 2) ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?= e(strtoupper($order['status'])) ?>
                                </span>
                            </td>
                            <td><?= e($order['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>

</body>

</html>