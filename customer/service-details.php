<?php

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("
    SELECT
        services.*,
        users.name AS student_name,
        categories.name AS category_name
    FROM services

    INNER JOIN users
        ON services.student_id = users.id

    INNER JOIN categories
        ON services.category_id = categories.id

    WHERE services.id = ?
    AND services.status = 'published'
");

$stmt->execute([$id]);

$service = $stmt->fetch();

if (!$service) {
    die("Service not found.");
}