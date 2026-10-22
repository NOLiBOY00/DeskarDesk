<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('customer');

$serviceId = filter_input(INPUT_GET, 'id',FILTER_VALIDATE_INT);

if (!$serviceId) {
    redirect('marketplace.php');
}


$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.description, c.name AS category, s.price,
        s.delivery_days, s.created_at, u.id AS student_id,
        u.name AS student_name, u.email AS student_email,
        sp.bio,
        sp.skills,
        sp.location
    FROM services s INNER JOIN users u
        ON u.id = s.student_id
    LEFT JOIN student_profiles sp
        ON sp.user_id = s.student_id
    JOIN categories c
        ON s.category_id = c.id
    WHERE s.id = ?
    AND s.status = 'active'
");

$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$service) {
    redirect('marketplace.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($service['title']) ?> - DiskarDesk</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../public/logo/logo_only.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk 
        </a>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="marketplace.php" class="btn btn-outline-light btn-sm">Marketplace</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>


<div class="container mt-5">

    <a href="marketplace.php" class="btn btn-secondary mb-4">← Back to Marketplace</a>

    <div class="row g-4">

        <!-- Service Information -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <span class="badge bg-primary mb-3"><?= e($service['category']) ?></span>
                    <h1><?= e($service['title']) ?></h1>
                    <hr>

                    <h5>Description</h5>
                    <p><?= nl2br(e($service['description'])) ?></p>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <strong>Price</strong>
                            <p class="fs-4">₱<?= number_format($service['price'],2) ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Delivery Time</strong>
                            <p class="fs-4"><?= (int) $service['delivery_days'] ?> day(s)</p>
                        </div>
                    </div>

                     <a href="request-service.php?id=<?= (int) $service['id'] ?>" class="btn btn-success btn-lg">
                        Request Service
                     </a> 
                </div>
            </div>
        </div>

        <!-- Student Information -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-body">
                    <h4>Student</h4>
                    <hr>

                    <h5><?= e($service['student_name']) ?></h5>

                    <?php if (!empty($service['location'])): ?>
                        <p><strong>Location:</strong> <?= e($service['location']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($service['skills'])): ?>
                        <p><strong>Skills:</strong> <?= e($service['skills']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($service['bio'])): ?>
                        <p><strong>About:</strong></p>
                        <p><?= nl2br(e($service['bio'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>