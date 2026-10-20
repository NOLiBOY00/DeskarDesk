<?php
//Customer Approves Work
$orderId = (int) $_POST['order_id'];

$stmt = $pdo->prepare("
    UPDATE orders
    SET
        status = 'completed',
        completed_at = NOW()
    WHERE id = ?
    AND customer_id = ?
    AND status = 'delivered'
");

$stmt->execute([
    $orderId,
    $_SESSION['user_id']
]);