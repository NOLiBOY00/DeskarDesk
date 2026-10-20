<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiskarDesk</title>
    <link rel="icon" href="public/logo/Logo_trns.png" type="image/x-icon">
    <link href="public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="public/logo/Logo_trns.png" width="40" height="40" alt="logo" class="d-inline-block align-text-top">
                DiskarDesk
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-lg-2" href="register.php">Register</a>
                        </li>
                    <?php else: ?>
                        <?php if ($_SESSION['role'] === 'student'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="student/dashboard.php">Dashboard</a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="customer/marketplace.php">Marketplace</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="customer/dashboard.php">Dashboard</a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="bg-light py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h1 class="display-4 fw-bold">Welcome to DiskarDesk</h1>
                    <p class="lead mt-3">
                        A student service marketplace where
                        students can offer their skills and
                        customers can request services.
                    </p>
                    <div class="mt-4">
                        <a href="customer/marketplace.php" class="btn btn-primary btn-lg">Browse Services</a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="register.php" class="btn btn-outline-dark btn-lg ms-2">Get Started</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-5 mt-4 mt-md-0">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="fw-bold">What can you do?</h3>
                            <hr>
                            <p><strong>Students</strong></p>
                            <p>
                                Create your profile, offer services,
                                manage orders, deliver work and
                                receive customer reviews.
                            </p>
                            <p><strong>Customers</strong></p>
                            <p>
                                Browse services, request a service,
                                communicate with students and
                                review completed work.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">DiskarDesk Features</h2>
                <p class="text-muted">Everything needed for the service marketplace.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h4>Student Services</h4>
                            <p class="text-muted">
                                Students can create and manage
                                their service offerings.
                            </p>

                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h4>Order Management</h4>
                            <p class="text-muted">
                                Customers can request services
                                and track their orders.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h4>Reviews & Messages</h4>
                            <p class="text-muted">
                                Customers and students can
                                communicate and exchange reviews.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> DiskarDesk</p>
        </div>
    </footer>

    <script src="public/js/framework/jquery-3.6.0.min.js"></script>
    <script src="public/js/framework/bootstrap.bundle.min.js"></script>

</body>

</html>