<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('customer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? '');

$customerId = $_SESSION['user_id'];
$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId) {
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("
    UPDATE orders
    SET
        status = 'completed',
        completed_at = NOW()
    WHERE id = ?
    AND customer_id = ?
    AND status = 'delivered'
");

$stmt->execute([$orderId, $customerId]);

redirect('dashboard.php');