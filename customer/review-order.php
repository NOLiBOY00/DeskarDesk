<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('customer');

$customerId = $_SESSION['user_id'];

$orderId = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT);


//------------------------------------
$stmt = $pdo->prepare("
    SELECT o.id, o.status, o.price,
        s.id AS service_id, s.title AS service_title, u.id AS student_id,
        u.name AS student_name
    FROM orders o
    INNER JOIN services s
        ON s.id = o.service_id
    INNER JOIN users u
        ON u.id = o.student_id
    WHERE o.id = ?
    AND o.customer_id = ?
    AND o.status = 'completed'
");

$stmt->execute([$orderId, $customerId]);
$order = $stmt->fetch();

if (!$order) {
    redirect('dashboard.php');
}

//---------------------------------------
$stmt = $pdo->prepare("
    SELECT id
    FROM reviews
    WHERE order_id = ?
    AND customer_id = ?
    LIMIT 1
");

$stmt->execute([$orderId, $customerId]);
$existingReview = $stmt->fetch();

if ($existingReview) {
    redirect('dashboard.php');
}

//------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

    $comment = trim($_POST['comment'] ?? '');

    if (!$rating || $rating < 1 || $rating > 5) {
        redirect('review-order.php?id=' . $orderId);
    }

    $stmt = $pdo->prepare("
        INSERT INTO reviews (order_id, service_id, student_id,
            customer_id, rating, comment)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([$order['id'], $order['service_id'], $order['student_id'],
        $customerId, $rating, $comment
    ]);

    redirect('dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Order - Diskardesk</title>
    <link rel="icon" href="../public/logo/Logo_trns.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Leave a Review</h2>
                    <p><strong>Service:</strong> <?= e($order['service_title']) ?></p>
                    <p><strong>Student:</strong> <?= e($order['student_name']) ?></p>
                    <hr>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select" required>
                                <option value="">Select Rating</option>
                                <option value="5">⭐⭐⭐⭐⭐ — Excellent</option>
                                <option value="4">⭐⭐⭐⭐ — Very Good</option>
                                <option value="3">⭐⭐⭐ — Good</option>
                                <option value="2">⭐⭐ — Poor</option>
                                <option value="1">⭐ — Very Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comment</label>
                            <textarea name="comment" class="form-control" rows="5"
                                     placeholder="Write your feedback..."></textarea>
                        </div>

                        <button type="submit"class="btn btn-primary">Submit Review</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>

</body>

</html>