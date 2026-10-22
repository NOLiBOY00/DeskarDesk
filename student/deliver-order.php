<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('orders.php');
}

$studentId = $_SESSION['user_id'];
$orderId = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT);

$deliveryMessage = trim($_POST['delivery_message'] ?? '');

if (!$orderId || $deliveryMessage === '') {
    redirect('orders.php');
}

$stmt = $pdo->prepare("
    SELECT o.id, o.price, o.status,
        s.title AS service_title, u.name AS customer_name
    FROM orders o
    INNER JOIN services s
        ON s.id = o.service_id
    INNER JOIN users u
        ON u.id = o.customer_id
    WHERE o.id = ?
    AND o.student_id = ?
    AND o.status = 'in_progress'
");

$stmt->execute([ $orderId, $studentId]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $deliveryMessage = trim($_POST['delivery_message'] ?? '');

    if ($deliveryMessage === '') {
        redirect('deliver-order.php?id=' . $orderId);
    }

    $stmt = $pdo->prepare("
        UPDATE orders
        SET
            status = 'delivered',
            delivery_message = ?,
            delivered_at = NOW()
        WHERE id = ?
        AND student_id = ?
        AND status = 'in_progress'
    ");

    $stmt->execute([ $deliveryMessage, $orderId, $studentId]);

    redirect('orders.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deliver Work - DiskarDesk</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Deliver Work</h2>
                    <p><strong>Service:</strong> <?= e($order['service_title']) ?></p>
                    <p><strong>Customer:</strong> <?= e($order['customer_name']) ?></p>
                    <p><strong>Price:</strong> ₱<?= number_format($order['price'], 2) ?></p>
                    <hr>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Delivery Message</label>
                            <textarea name="delivery_message" class="form-control" rows="6" required
                                placeholder="Write a message to the customer..."></textarea>
                        </div>
                        <button type="submit"class="btn btn-success">Deliver Work</button>
                        <a href="orders.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>