<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('services.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? '');

$userId = $_SESSION['user_id'];

// --------------------------------
// GET SERVICE ID
// --------------------------------
$serviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$serviceId) {
    redirect('services.php');
}

// --------------------------------
// DELETE
// --------------------------------
$stmt = $pdo->prepare("
    DELETE FROM services
    WHERE id = ?
    AND student_id = ?
");

$stmt->execute([$serviceId,$userId]);

redirect('services.php');