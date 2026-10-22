<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

// Total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");

$totalUsers = (int) $stmt->fetchColumn();

// Total student
$stmt = $pdo->query("
    SELECT COUNT(*) FROM users
    WHERE role = 'student'
");

$totalStudents = (int) $stmt->fetchColumn();

// Total customer
$stmt = $pdo->query("
    SELECT COUNT(*) FROM users
    WHERE role = 'customer'
");

$totalCustomers = (int) $stmt->fetchColumn();

// Total service
$stmt = $pdo->query("SELECT COUNT(*) FROM services");

$totalServices = (int) $stmt->fetchColumn();

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");

$totalOrders = (int) $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - DiskarDesk</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a href="dashboard.php" class="navbar-brand">
            <img src="../public/logo/logo_only.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk Admin
        </a>
        <div>
            <a href="users.php" class="btn btn-primary">Users</a>
            <a href="services.php" class="btn btn-success">Services</a>
            <a href="orders.php" class="btn btn-warning">Orders</a>
            <a href="support.php" class="btn btn-outline-light">Disputes & Support</a>
            <a href="../logout.php" class="btn btn-outline-light">Logout</a>
            
        </div>

    </div>
</nav>


<div class="container py-5">
    <h1 class="mb-4">Admin Dashboard</h1>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Total Users</h5>
                    <h2><?= $totalUsers ?></h2>
                    <a href="users.php" class="btn btn-primary">View Users</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Students</h5>
                    <h2><?= $totalStudents ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Customers</h5>
                    <h2><?= $totalCustomers ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Total Services</h5>
                    <h2><?= $totalServices ?></h2>
                    <a href="services.php" class="btn btn-success">View Services</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Total Orders</h5>
                    <h2><?= $totalOrders ?></h2>
                    <a href="orders.php" class="btn btn-warning">View Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>