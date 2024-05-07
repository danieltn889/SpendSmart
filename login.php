<?php
session_start();

// Redirect user to dashboard if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';
$spendSmart=new SpendSmart();
$pdo=$spendSmart->getPDO();

// Check if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query database to verify user credentials
    $sql = "SELECT * FROM Users WHERE phone = :phone";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':phone', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    $pin=md5($password);
    if($user && $pin==$user['pin']) {
        $_SESSION['user_id'] = $user['userId'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Login</h2>
        <?php if(isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        <form action="" method="post" class="mt-3">
            <div class="form-group">
                <label for="username">Phone Number:</label>
                <input type="text" id="username" name="username" class="form-control" pattern="[0-9]{10}" title="Please enter a 10-digit phone number">

            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>&nbsp&nbsp<a href="signup.php" class="btn btn-success">Sign Up</a>
        </form>
    </div>
    <!-- Bootstrap JS (optional, if you need JavaScript features) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
