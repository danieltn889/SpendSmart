<?php
session_start();

// Redirect user to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Fetch resource ID from the URL parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list_resources_available.php");
    exit;
}
$resourceAvId = $_GET['id'];

// Fetch resource details
$sql = "SELECT ra.re_av_id, ra.re_id, r.re_name, ra.qty 
        FROM resourcesavailable ra
        INNER JOIN resources r ON ra.re_id = r.re_id 
        WHERE ra.re_av_id = :re_av_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':re_av_id', $resourceAvId);
$stmt->execute();
$resource = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantity = $_POST['quantity'];
    
    // Validate quantity input
    if (!is_numeric($quantity) || $quantity <= 0) {
        $error = "Quantity must be a positive number.";
    } else {
        // Update the resourcesavailable table to add the entered quantity
        $updateSql = "UPDATE resourcesavailable SET qty = qty + :quantity WHERE re_av_id = :re_av_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $updateStmt->bindParam(':re_av_id', $resourceAvId, PDO::PARAM_INT);
        $updateStmt->execute();

        // Insert a new record into the historical table with status "Addition"
        $insertSql = "INSERT INTO historical (re_id, quantity, operation, date, status) 
                      VALUES (:re_id, :quantity, 'Addition', CURDATE(), 'completed')";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->bindParam(':re_id', $resource['re_id'], PDO::PARAM_INT);
        $insertStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $insertStmt->execute();

        // Redirect back to the resource page after saving
        header("Location: list_resources_available.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Resource</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
    <h2>Save Resource</h2>
    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="save.php?id=<?php echo $resourceAvId; ?>" method="post">
        <div class="form-group">
            <label for="quantity">Quantity to Add:</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
        <a href="list_resources_available.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
