<?php
// index.php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Redirect to the dashboard if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Platform</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container text-center mt-5">
        <h1>Welcome to the Learning Platform</h1>
        <p class="lead">Please log in or register to access the platform.</p>
        <div class="mt-4">
            <a href="pages/login.php" class="btn btn-primary btn-lg">Login</a>
            <a href="pages/register.php" class="btn btn-success btn-lg">Register</a>
        </div>
    </div>
</body>
</html>