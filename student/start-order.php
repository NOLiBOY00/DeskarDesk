<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('orders.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? '');

$studentId = $_SESSION['user_id'];
$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId) {
    redirect('orders.php');
}

$stmt = $pdo->prepare("
    UPDATE orders
    SET status = 'in_progress'
    WHERE id = ?
    AND student_id = ?
    AND status = 'approved'
");

$stmt->execute([$orderId, $studentId]);

redirect('orders.php');