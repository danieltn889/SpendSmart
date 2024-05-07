<?php
session_start();

// Redirect user to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Fetch user ID from session
$userId = $_SESSION['user_id'];

// Fetch resources associated with the logged-in user
$sql = "SELECT * FROM resources WHERE userId = :userId";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':userId', $userId);
$stmt->execute();
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Resources</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>List of Resources</h2>
    <a href="add_resource.php" class="btn btn-primary mb-3">Add Resource</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th colspan="2">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resources as $resource): ?>
                <tr>
                    <td><?php echo $resource['re_id']; ?></td>
                    <td><?php echo $resource['re_name']; ?></td>
                    <td><?php echo $resource['re_status']; ?></td>
                    <td><a href="edit_resource.php?id=<?php echo $resource['re_id']; ?>" class="btn btn-primary btn-sm">Edit</a></td>
                    <td> <a href="delete_resource.php?id=<?php echo $resource['re_id']; ?>" class="btn btn-danger btn-sm">Delete</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
