<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // -------------------------
    // VALIDATION
    // -------------------------

    if ($name === '' || $email === '' || $password === '' || $role === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!in_array($role,['student', 'customer'],true)) {
        $error = "Invalid account type.";
    } elseif($role === 'student' && strtolower( substr(strrchr($email, '@') ?: '', 1) ) !== 'usep.edu.ph') {
        $error = 'Student registration requires an @usep.edu.ph email address.';
    } else {
        // -------------------------
        // CHECK EMAIL
        // -------------------------
        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?");

        $stmt->execute([$email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $error = "This email is already registered.";
        } else {
            // -------------------------
            // HASH PASSWORD
            // -------------------------
            $passwordHash = password_hash($password,PASSWORD_DEFAULT);

            // -------------------------
            // INSERT USER
            // -------------------------
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, ?)");

            $stmt->execute([$name, $email,$passwordHash,$role]);
            $userId = $pdo->lastInsertId();

            // -------------------------
            // CREATE STUDENT PROFILE
            // -------------------------
            if ($role === 'student') {
                $stmt = $pdo->prepare("
                    INSERT INTO student_profiles (user_id)
                    VALUES (?)
                ");

                $stmt->execute([$userId]);
            }

            // -------------------------
            // SUCCESS
            // -------------------------
            redirect('login.php');
        }
    }
}
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - DiskarDesk</title>
    <link rel="icon" href="public/logo/Logo_trns.png" type="image/x-icon">
    <link href="public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">

            <div class="col-md-6">

                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Create DiskarDesk Account</h2>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= e($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- NAME -->
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control"required>
                            </div>
                            <!-- EMAIL -->
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                                <div class="form-text" id="emailHelp">
                                    Students must use their USeP email address.
                                    Customer may use their own valid email address.
                                </div>
                            </div>
                            <!-- PASSWORD -->
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <!-- ROLE -->
                            <div class="mb-3">
                                <label class="form-label">I want to...</label>
                                <select name="role" class="form-select" required>
                                    <option value="">Select account type</option>
                                    <option value="student">Offer Skills (Student/Seller)</option>
                                    <option value="customer">Find Skills (Customer/Buyer)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Account</button>
                        </form>

                        <div class="text-center mt-3">Already have an account? <a href="login.php">Login</a></div>
                    </div>
                </div>

            </div>

        </div>
    </div>

</body>

</html>