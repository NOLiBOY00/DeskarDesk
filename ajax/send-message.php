<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'seccess' => false,
        'message' => 'Invalid request.'
    ]);
    exit;
}

try {

    verifyCsrfToken($_POST['csrf_token'] ?? '');

    // Send message
    $userId = $_SESSION['user_id'];
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $message = trim($_POST['message'] ?? '');

    if (!$orderId || $message === '') {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid message.'
        ]);

        exit;
    }

    // Verify ownership
    $stmt = $pdo->prepare("
        SELECT id, student_id, customer_id, status
        FROM orders
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
            'message' => 'You do not have access to this order.'
        ]);

        exit;
    }

    // Determine the reciever
    if ($userId == $order['student_id']) {
        $receiverId = $order['customer_id'];
    } else {
        $receiverId = $order['student_id'];
    }

    // Insert meesage
    $stmt = $pdo->prepare("
        INSERT INTO messages (order_id, sender_id, receiver_id, message)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$orderId, $userId, $receiverId, $message]);
    echo json_encode([
        'success' => true,
        'message' => 'Message sent.'
    ]);

    // Allowed status
    $allowedStatuses = [
        'pending', 'approved','in_progress', 'delivered'
    ];

    if (!in_array($order['status'], $allowedStatuses, true)) {

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Messaging is not available for this order.'
        ]);
        exit;
    }

} catch(Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to send message.'
    ]);
}