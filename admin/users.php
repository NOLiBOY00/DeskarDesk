<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$stmt = $pdo->query("
    SELECT id, name, email,
        role, created_at
    FROM users
    ORDER BY created_at DESC
");

$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users - DiskarDesk Admin</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand"><img src="../public/logo/logo_only.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
               Users
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= (int) $user['id'] ?></td>
                            <td><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= e($user['role']) ?></span>
                            </td>
                            <td><?= e($user['created_at']) ?></td>
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