<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$stmt = $pdo->query("
    SELECT s.id, s.title, c.name AS category,
        s.price, s.delivery_days, s.status,
        s.created_at, u.name AS student_name
    FROM services s
    INNER JOIN users u
        ON u.id = s.student_id
    JOIN categories c
        ON s.category_id = c.id
    ORDER BY s.created_at DESC
");

$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services - DiskarDesk Admin</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
               <a class="navbar-brand"><img src="../public/logo/logo_only.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
               Services
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
                            <th>Category</th>
                            <th>Price</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?= (int) $service['id'] ?></td>
                            <td><?= e($service['title']) ?></td>
                            <td><?= e($service['student_name']) ?></td>
                            <td><?= e($service['category']) ?></td>
                            <td>₱<?= number_format($service['price'], 2) ?></td>
                            <td><?= (int) $service['delivery_days'] ?></td>
                            <td>
                                <?php if ($service['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
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