<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId) {

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid order.'
    ]);

    exit;
}

//Verify access
$stmt = $pdo->prepare("
    SELECT id FROM orders
    WHERE id = ?
    AND (
        student_id = ?
        OR customer_id = ?
    )
");

$stmt->execute([$orderId, $userId, $userId]);
$order = $stmt->fetch();

if (!$order) {

    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);

    exit;
}

// Retrieve messages
$stmt = $pdo->prepare("
    SELECT m.id, m.sender_id, m.receiver_id,
        m.message, m.created_at, u.name AS sender_name
    FROM messages m
    INNER JOIN users u
        ON u.id = m.sender_id
    WHERE m.order_id = ?
    ORDER BY m.created_at ASC
");

$stmt->execute([$orderId]);
$messages = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'messages' => $messages
]);