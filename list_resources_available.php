<?php
session_start();

// Redirect user to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Fetch resources available for the logged-in user
$userId = $_SESSION['user_id'];
$sql = "SELECT ra.*,r.*,ra.re_av_id, r.re_name, ra.qty 
        FROM resourcesavailable ra
        INNER JOIN resources r ON ra.re_id = r.re_id 
        WHERE r.userId = :userId group by ra.re_av_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Resources</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Available Resources</h2>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Resource Name</th>
                <th>Quantity</th>
                <th colspan="2">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resources as $key => $resource) : ?>
            <tr>
                <td><?php echo $key + 1; ?></td>
                <td><?php echo $resource['re_name']; ?></td>
                <td><?php echo $resource['qty']; ?></td>
                <td>
                    <a href="spend.php?id=<?php echo $resource['re_av_id']; ?>" class="btn btn-primary">Export</a>
                </td>
                <td>
                    <a href="save.php?id=<?php echo $resource['re_av_id']; ?>" class="btn btn-success">Import</a>
                </td>
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
