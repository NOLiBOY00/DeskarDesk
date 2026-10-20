<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$userId = $_SESSION['user_id'];
$error = '';

// --------------------------------
// FOR categories
// --------------------------------
$stmt = $pdo->query("
        SELECT id, name FROM categories
        ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --------------------------------
// GET SERVICE ID
// --------------------------------
$serviceId = filter_input(INPUT_GET, 'id',FILTER_VALIDATE_INT);

if (!$serviceId) {
    redirect('services.php');
}

// --------------------------------
// GET SERVICE
// --------------------------------
$stmt = $pdo->prepare("
    SELECT s.id, title, description, c.name AS category,
        price, delivery_days, status
    FROM services s JOIN categories c
        ON s.category_id = c.id
    WHERE s.id = ?
    AND student_id = ?
");

$stmt->execute([$serviceId, $userId]);
$service = $stmt->fetch();


if (!$service) {
    http_response_code(404);
    die("Service not found.");
}


// --------------------------------
// UPDATE
// --------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $price = $_POST['price'] ?? '';
    $deliveryDays = $_POST['delivery_days'] ?? '';
    $status = $_POST['status'] ?? 'active';

    verifyCsrfToken($_POST['csrf_token'] ?? '');

    // --------------------------------
    // VALIDATION
    // --------------------------------
    if ($title === '' || $description === '' || $category_id === '' || 
        $price === '' || $deliveryDays === '') {
        $error = "All fields are required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Please enter a valid price.";
    } elseif (!filter_var($deliveryDays, FILTER_VALIDATE_INT) ||
             $deliveryDays < 1  ) {
        $error = "Delivery days must be at least 1.";

    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Invalid status.";
    } else {
        // --------------------------------
        // UPDATE DATABASE
        // --------------------------------
        $stmt = $pdo->prepare("
            UPDATE services
            SET
                title = ?,
                description = ?,
                category_id = ?,
                price = ?,
                delivery_days = ?,
                status = ?
            WHERE id = ?
            AND student_id = ?
        ");

        $stmt->execute([$title, $description, $category_id, $price,
                 $deliveryDays, $status, $serviceId, $userId
        ]);
        redirect('services.php');
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Service - DiskarDesk</title>
    <link rel="icon" href="../public/logo/Logo_trns.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css"rel="stylesheet">
</head>

<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../public/logo/Logo_trns.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk 
        </a>        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="services.php" class="btn btn-outline-light btn-sm">My Services</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>


<div class="container mt-4">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h4 class="mb-0">Edit Service</h4>
                </div>

                <div class="card-body">

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <div class="mb-3">
                            <label class="form-label">Service Title</label>
                            <input type="text" name="title" class="form-control"
                                value="<?= e($service['title']) ?>" required>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="5"
                                required><?= e($service['description']) ?></textarea>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>

                                <option value="">Select Category</option>

                                <?php foreach ( $categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $service['category'] === $category['name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" name="price" class="form-control" min="0" step="0.01"
                                value="<?= e($service['price']) ?>" required>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Delivery Time (days)</label>
                            <input type="number" name="delivery_days" class="form-control" min="1"
                                value="<?= e($service['delivery_days']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $service['status'] === 'active' ? 'selected': '' ?>>Active</option>
                                <option value="inactive" <?= $service['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <button type="submit"class="btn btn-primary">Save Changes</button>

                        <a href="services.php" class="btn btn-secondary">Cancel</a>
                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>