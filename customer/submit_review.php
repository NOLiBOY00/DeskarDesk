<?php
// Customer Leaves Review
$rating = (int) $_POST['rating'];
$comment = trim($_POST['comment']);
$orderId = (int) $_POST['order_id'];

$stmt = $pdo->prepare("
    SELECT * FROM orders
    WHERE id = ?
    AND customer_id = ?
    AND status = 'completed'
");

$stmt->execute([
    $orderId,
    $_SESSION['user_id']
]);

$order = $stmt->fetch();

if (!$order) {
    die("Invalid order.");
}

$stmt = $pdo->prepare("
    INSERT INTO reviews
    (order_id, customer_id, student_id, rating, comment)
    VALUES 
    (?, ?, ?, ?, ?)");

$stmt->execute([
    $orderId,
    $_SESSION['user_id'],
    $order['student_id'],
    $rating,
    $comment
]);