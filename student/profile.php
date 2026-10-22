<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('student');

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// --------------------------------
// SAVE PROFILE
// --------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $location = trim($_POST['location'] ?? '');

    // Update profile
    $stmt = $pdo->prepare("
        UPDATE student_profiles
        SET
            bio = ?,
            skills = ?,
            location = ?
        WHERE user_id = ?
    ");

    $stmt->execute([$bio, $skills, $location, $userId]);
    $success = "Profile updated successfully.";
}

// --------------------------------
// GET PROFILE
// --------------------------------
$stmt = $pdo->prepare("
    SELECT u.name, u.email, sp.bio, sp.skills, sp.location
    FROM users u
    LEFT JOIN student_profiles sp
        ON sp.user_id = u.id
    WHERE u.id = ?
");

$stmt->execute([$userId]);
$student = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile - DiskarDesk</title>
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
                    <h4 class="mb-0">My Student Profile</h4>
                </div>

                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= e($success) ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>


                    <!-- ACCOUNT INFORMATION -->
                    <div class="mb-4">
                        <h5>Account Information</h5>
                        <p><strong>Name:</strong> <?= e($student['name']) ?></p>
                        <p><strong>Email:</strong> <?= e($student['email']) ?></p>
                    </div>

                    <hr>

                    <!-- PROFILE FORM -->
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">About Me</label>
                            <textarea name="bio" class="form-control" rows="5" 
                                        placeholder="Tell customers about yourself..."><?= e($student['bio']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Skills</label>
                            <input type="text" name="skills" class="form-control" value="<?= e($student['skills']) ?>"
                                    placeholder="Example: Mathematics, Graphic Design, Tutoring">
                            <div class="form-text">Separate multiple skills with commas.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="<?= e($student['location']) ?>"
                                    placeholder="Example: Davao City">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Profile</button>


                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

</body>

</html>