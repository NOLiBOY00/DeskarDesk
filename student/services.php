<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$userId = $_SESSION['user_id'];

// Get student's services
$stmt = $pdo->prepare("
    SELECT s.id, title, description, price, c.name AS category,
            delivery_days, status, s.created_at
    FROM services s JOIN categories c
        ON s.category_id = c.id
    WHERE student_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$userId]);
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Services - DiskarDesk</title>
    <link rel="icon" href="../public/logo/Logo_trns.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../public/logo/Logo_trns.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk 
        </a>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="profile.php" class="btn btn-outline-light btn-sm">Profile</a>
            <a href="services.php" class="btn btn-light btn-sm">My Services</a>
            <a href="orders.php" class="btn btn-outline-light btn-sm">Orders</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>


<!-- MAIN -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>My Services</h2>
            <p class="text-muted">Manage the services you offer.</p>
        </div>
        <a href="add-service.php" class="btn btn-primary">+ Add Service</a>
    </div>


    <?php if (!$services): ?>
        <div class="alert alert-info mt-4">
            You haven't created any services yet.
            <a href="add-service.php">Create your first service.</a>
        </div>
    <?php else: ?>

        <div class="row mt-3">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-4 mb-4">

                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= e($service['title']) ?></h5>
                            <span class="badge bg-secondary"><?= e($service['category']) ?></span>

                            <p class="card-text mt-3"><?= e($service['description']) ?></p>

                            <h5>₱<?= number_format($service['price'], 2) ?></h5>

                            <p class="text-muted">Delivery: <?= e($service['delivery_days']) ?> day(s)</p>


                            <?php if ($service['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>

                        </div>

                        <div class="card-footer bg-white">
                            <a href="edit-service.php?id=<?= e($service['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                            <form method="POST" action="delete-service.php" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this service?');">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

</body>
</html>