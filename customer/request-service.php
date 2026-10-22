<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('customer');

$customerId = $_SESSION['user_id'];

$serviceId = filter_input(INPUT_GET, 'id',FILTER_VALIDATE_INT);

if (!$serviceId) {
    redirect('marketplace.php');
}

/*
|--------------------------------------------------------------------------
| Get active service
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.description,
        s.price, s.delivery_days, s.student_id, u.name AS student_name
    FROM services s
    INNER JOIN users u
        ON u.id = s.student_id
    WHERE s.id = ?
    AND s.status = 'active'
");

$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    redirect('marketplace.php');
}

/*
|--------------------------------------------------------------------------
| Handle request
|--------------------------------------------------------------------------
*/
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    /*
    |--------------------------------------------------------------------------
    | Check if customer already has an active request
    |--------------------------------------------------------------------------
    */
    $checkStmt = $pdo->prepare("
        SELECT id FROM orders
        WHERE service_id = ?
        AND customer_id = ?
        AND status IN ('pending', 'approved', 'in_progress', 'delivered')
        LIMIT 1
    ");

    $checkStmt->execute([$serviceId, $customerId]);
    $existingOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);


    if ($existingOrder) {
        $error = 'You already have an active request for this service.';
    } else {
        /*
        |--------------------------------------------------------------------------
        | Create order
        |--------------------------------------------------------------------------
        */
        $insertStmt = $pdo->prepare("
            INSERT INTO orders (service_id, customer_id, student_id, price,
                status, request_message)
            VALUES (?, ?, ?, ?, 'pending', ?)
        ");

        $insertStmt->execute([$service['id'], $customerId, $service['student_id'],
                $service['price'], $message
        ]);

        redirect('dashboard.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Service - DISKARDESK</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css"rel="stylesheet">
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

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow">

                <div class="card-body">
                    <h2 class="mb-4">Request Service</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h4><?= e($service['title']) ?></h4>
                        <p class="text-muted">
                            Offered by <strong><?= e($service['student_name']) ?></strong>
                        </p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Price</strong>
                            <p class="fs-4">₱<?= number_format($service['price'],2) ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Delivery</strong>
                            <p class="fs-4"><?= (int) $service['delivery_days'] ?> day(s)</p>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-4">
                            <label for="message" class="form-label">Message to Student</label>
                            <textarea name="message" id="message" class="form-control"
                                rows="5" placeholder="Explain what you need..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="view-service.php?id=<?= (int) $service['id'] ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Send Request</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>


</body>
</html>