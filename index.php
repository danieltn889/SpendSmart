<?php
// Redirect logged-in users to dashboard
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            padding: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to SpendSmart</h1>
        <p class="lead">Please log in or sign up to manage your resources.</p>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <a href="login.php" class="btn btn-primary btn-lg btn-block">Login</a>
            </div>
            <div class="col-md-4">
                <a href="signup.php" class="btn btn-success btn-lg btn-block">Sign Up</a>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS (optional, if you need JavaScript features) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
