<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $categoryId = (int) $_POST['category_id'];
    $price = (float) $_POST['price'];
    $deliveryDays = (int) $_POST['delivery_days'];

    if ($title === '' || $description === '' ||
        $price <= 0 || $deliveryDays <= 0) {
        die("Invalid service information.");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO services (student_id, category_id, title, description,
                                price, delivery_days,status)
        VALUES (?, ?, ?, ?, ?, ?, 'published')"
    );

    $stmt->execute([$_SESSION['user_id'], $categoryId, $title,
        $description, $price,$deliveryDays
    ]);

    header("Location: services.php");
    exit;
}