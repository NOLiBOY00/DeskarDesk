<?php

session_start();

require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // -------------------------
    // BASIC VALIDATION
    // -------------------------
    if ($email === '' || $password === '') {
        $error = "Email and password are required.";
    } else {
        // -------------------------
        // FIND USER
        // -------------------------
        $stmt = $pdo->prepare("
            SELECT id, name, email, password, role
            FROM users
            WHERE email = ?
        ");

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // -------------------------
        // VERIFY PASSWORD
        // -------------------------
        if ($user && password_verify($password,$user['password'])) {
            // -------------------------
            // PREVENT SESSION FIXATION
            // -------------------------
            session_regenerate_id(true);

            // -------------------------
            // CREATE SESSION
            // -------------------------
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // -------------------------
            // REDIRECT BY ROLE
            // -------------------------
            if ($user['role'] === 'student') {
                redirect('student/dashboard.php');
            } elseif ($user['role'] === 'customer') {
                redirect('customer/dashboard.php');
            } elseif ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - DiskarDesk</title>
    <link rel="icon" href="public/logo/logo_only.png" type="image/x-icon">
    <link href="public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5 py-5">
        <div class="row justify-content-center align-item-center g-4">

            <div class="justify-content-center col-md-4 text-center">
                    <img class="img-fluid" src="public/logo/w_txt.png" width="400" height="400" alt="logo">
            </div>

            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-4">

                        <h2 class="text-center mb-4">DiskarDesk Login</h2>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= e($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit"class="btn btn-primary w-100">Login</button>
                        </form>

                        <div class="text-center mt-3">Don't have an account? <a href="register.php">Register</a></div>

                    </div>
                </div>
            </div>

        </div>
    </div>

</body>

</html>