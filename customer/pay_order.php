<?php
// Customer Pays
$orderId = (int) $_POST['order_id'];

$stmt = $pdo->prepare("
    UPDATE orders
    SET
        payment_status = 'paid',
        status = 'in_progress'
    WHERE id = ?
    AND customer_id = ?
    AND status = 'approved'
    AND payment_status = 'unpaid'
");

$stmt->execute([
    $orderId,
    $_SESSION['user_id']
]);