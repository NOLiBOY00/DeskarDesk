<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('student');

$userId = $_SESSION['user_id'];

// Get student information
$stmt = $pdo->prepare("
    SELECT u.name, u.email, u.role, sp.bio, sp.skills, sp.location
    FROM users u
    LEFT JOIN student_profiles sp
        ON sp.user_id = u.id
    WHERE u.id = ?
");

$stmt->execute([$userId]);
$student = $stmt->fetch();

// Count services
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM services
    WHERE student_id = ?
");

$stmt->execute([$userId]);
$serviceCount = $stmt->fetchColumn();

// Count pending orders
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM orders
    WHERE student_id = ? AND status = 'pending'
");

$stmt->execute([$userId]);
$pendingOrders = $stmt->fetchColumn();


// Count completed orders
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM orders
    WHERE student_id = ? AND status = 'completed'
");

$stmt->execute([$userId]);
$completedOrders = $stmt->fetchColumn();

// Add average ratings
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS review_count,
        AVG(rating) AS average_rating
    FROM reviews
    WHERE student_id = ?
");

$stmt->execute([$userId]);
$ratingStats = $stmt->fetch();

$reviewCount = (int) $ratingStats['review_count'];

$averageRating = $ratingStats['average_rating']
    ? round((float) $ratingStats['average_rating'], 2) : 0;
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard - DiskarDesk</title>
    <link rel="icon" href="../public/logo/Logo_trns.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark">

    <div class="container">
        <a class="navbar-brand">
            <img src="../public/logo/Logo_trns.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk 
        </a>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="profile.php" class="btn btn-outline-light btn-sm">Profile</a>
            <a href="services.php" class="btn btn-outline-light btn-sm">My Service</a>
            <a href="orders.php" class="btn btn-outline-light btn-sm">Orders</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="container mt-4">

    <h2>Student Dashboard</h2>
    <p class="text-muted">Welcome back, <?= e($student['name']) ?>!</p>


    <!-- STATISTICS -->
    <div class="row g-3 mt-3">

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">My Services</h6>
                    <h2><?= e($serviceCount) ?></h2>
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Pending Orders</h6>
                    <h2><?= e($pendingOrders) ?></h2>
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Completed Orders</h6>
                    <h2><?= e($completedOrders) ?></h2>
                </div>
            </div>
        </div>

    </div>


    <!-- PROFILE SUMMARY -->
    <div class="card shadow-sm mt-4">
        <div class="card-header">My Profile</div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= e($student['name']) ?></p>
            <p><strong>Email:</strong> <?= e($student['email']) ?></p>
            <p><strong>Skills:</strong> <?= e($student['skills'] ?: 'No skills added yet.') ?></p>
            <p><strong>Location:</strong> <?= e($student['location'] ?: 'Not specified.') ?></p>
            <p><strong>Bio:</strong> <?= e($student['bio'] ?: 'No biography added yet.') ?></p>
            <a href="profile.php" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Rating</h5>
            <?php if ($reviewCount > 0): ?>
                <h3>⭐ <?= number_format($averageRating, 2) ?></h3>
                <p class="text-muted mb-0"><?= $reviewCount ?> review(s)</p>
            <?php else: ?>
                <p class="text-muted mb-0">No reviews yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>

</html>