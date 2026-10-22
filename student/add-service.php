<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$error = '';

// --------------------------------
// FOR categories
// --------------------------------
$stmt = $pdo->query("
        SELECT id, name FROM categories
        ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --------------------------------
// HANDLE FORM
// --------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $price = $_POST['price'] ?? '';
    $deliveryDays = $_POST['delivery_days'] ?? '';

    // --------------------------------
    // VALIDATION
    // --------------------------------
    if ($title === '' || $description === '' ||$category_id === '' ||
        $price === '' ||$deliveryDays === '') {

        $error = "All fields are required.";
    } elseif (strlen($title) > 150) {
        $error = "Service title is too long.";
    } elseif (!is_numeric($price) ||$price < 0) {
        $error = "Please enter a valid price.";
    } elseif (!filter_var($deliveryDays, FILTER_VALIDATE_INT) ||$deliveryDays < 1) {
        $error = "Delivery days must be at least 1.";
    } else {
        // --------------------------------
        // INSERT SERVICE
        // --------------------------------
        $stmt = $pdo->prepare("
            INSERT INTO services (student_id, category_id, title, 
                                    description, price, delivery_days)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description,
             $price, $deliveryDays]);
        redirect('services.php');
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Service - DiskarDesk</title>
    <link rel="icon" href="../public/logo/logo_only.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>
<body>


<nav class="navbar navbar-dark bg-dark">

    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../public/logo/logo_only.png" width="30" height="30" alt="logo" class="d-inline-block align-text-top">
            DiskarDesk 
        </a>
        <div>
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
                    <h4 class="mb-0">Create New Service</h4>
                </div>

                <div class="card-body">

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">

                        <!-- TITLE -->
                        <div class="mb-3">
                            <label class="form-label">Service Title</label>
                            <input type="text" name="title" class="form-control" maxlength="150"
                                placeholder="Example: Basic Mathematics Tutoring" value="<?= e($_POST['title'] ?? '') ?>" required>
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="5" 
                                    placeholder="Describe what you offer..."required><?= e($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <!-- CATEGORY -->
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>

                                <option value="">Select Category</option>

                                <?php foreach($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach ?>

                            </select>
                        </div>

                        <!-- PRICE -->
                        <div class="mb-3">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" name="price" class="form-control" min="0" step="0.01"
                                placeholder="200.00" value="<?= e($_POST['price'] ?? '') ?>" required>
                        </div>

                        <!-- DELIVERY -->
                        <div class="mb-3">
                            <label class="form-label">Delivery Time (days)</label>
                            <input type="number" name="delivery_days" class="form-control" min="1" placeholder="2"
                                    value="<?= e($_POST['delivery_days'] ?? '') ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Service</button>
                        <a href="services.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>

            </div>

        </div>
    </div>
</div>

</body>

</html>