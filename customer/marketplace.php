<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('customer');

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$stmt = $pdo->query("
        SELECT id, name FROM categories
        ORDER BY name");
$ListCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT s.id, s.title, s.description, c.name AS category,
        s.price, s.delivery_days, u.name AS student_name, sp.location,
        review_stats.average_rating, review_stats.review_count
    FROM services s INNER JOIN users u
        ON u.id = s.student_id
    LEFT JOIN student_profiles sp
        ON sp.user_id = s.student_id
    JOIN categories c
        ON s.category_id = c.id
    LEFT JOIN (
        SELECT
            service_id,
            AVG(rating) AS average_rating,
            COUNT(*) AS review_count
        FROM reviews
        GROUP BY student_id
    ) review_stats
        ON review_stats.service_id = s.id
    WHERE s.status = 'active'
";

$params = [];

if ($search !== '') { // like searching parameter this part.

    $sql .= "
        AND (
            s.title LIKE ?
            OR s.description LIKE ?
            OR c.name LIKE ?
            OR u.name LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

if ($category !== '') {

    $sql .= "
        AND c.name = ?
    ";

    $params[] = $category;
}

$sql .= "
    ORDER BY s.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - DiskarDesk</title>
    <link rel="icon" href="../public/logo/Logo_trns.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css"rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../public/logo/Logo_trns.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk 
        </a>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="marketplace.php" class="btn btn-light btn-sm">Marketplace</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>


<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Marketplace</h1>
            <p class="text-muted">Find services offered by students.</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search services..." value="<?= e($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">

                            <option value="">All Categories</option>

                            <?php foreach ($ListCategories as $list) :?>
                                <option value="<?= $list['name'] ?>" 
                                    <?= $category === $list['name'] ? 'selected' : '' ?> ><?= $list['name'] ?></option>
                            <?php endforeach ?>

                            <option value="Other" <?= $category === 'Other' ? 'selected' : '' ?>>Other</option>

                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Services -->

    <div class="row g-4">

        <?php if (count($services) === 0): ?>
            <div class="col-12">
                <div class="alert alert-info">No services found.</div>
        </div>
        <?php endif; ?>


        <?php foreach ($services as $service): ?>
            <div class="col-md-6 col-lg-4">

                <div class="card h-100 shadow-sm">

                    <div class="card-body">
                        <span class="badge bg-secondary mb-2"><?= e($service['category']) ?></span>
                        <h4 class="card-title"><?= e($service['title']) ?></h4>

                        <p class="card-text"><?= e($service['description']) ?></p>
                        <p class="mb-1">
                            <strong>Student:</strong> <?= e($service['student_name']) ?>
                        </p>

                        <?php if (!empty($service['location'])): ?>
                            <p class="mb-1">
                                <strong>Location:</strong> <?= e($service['location']) ?>
                            </p>
                        <?php endif; ?>

                        <p class="mb-1">
                            <strong>Price:</strong> ₱<?= number_format($service['price'], 2) ?>
                        </p>
                        <p class="mb-3">
                            <strong>Delivery:</strong> <?= (int) $service['delivery_days'] ?> day(s)
                        </p>

                            <?php if (!empty($service['review_count'])): ?>
                                <div class="text-warning">
                                    ⭐ <?= number_format((float) $service['average_rating'],2) ?>
                                    <span class="text-muted">(<?= (int) $service['review_count'] ?> reviews)</span>
                                </div>
                            <?php else: ?>
                                <small class="text-muted">No reviews yet</small>
                            <?php endif; ?>
                        <a href="view-service.php?id=<?= (int) $service['id'] ?>" class="btn btn-primary w-100">View Service</a>

                    </div>

                </div>

            </div>
        <?php endforeach; ?>

    </div>

</div>

</body>
</html>