<?php
session_start();

// Redirect user to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if the ID parameter is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list_resources_available.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Fetch resource available for the logged-in user
$userId = $_SESSION['user_id'];
$resourceAvId = $_GET['id'];
$sql = "SELECT r.re_id,ra.qty, r.re_name 
        FROM resourcesavailable ra
        INNER JOIN resources r ON ra.re_id = r.re_id 
        WHERE ra.re_av_id = :re_av_id AND r.userId = :userId";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':re_av_id', $resourceAvId, PDO::PARAM_INT);
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();
$resource = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the resource is available and belongs to the logged-in user
if (!$resource) {
    header("Location: list_resources_available.php");
    exit;
}

// Process spending action
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate quantity input
    if (empty(trim($_POST["quantity"])) || !is_numeric($_POST["quantity"]) || (int)$_POST["quantity"] <= 0) {
        $quantity_err = "Please enter a valid quantity.";
    } else {
        $quantity = (int)$_POST["quantity"];
        
        // Check if the requested quantity is available
        if ($quantity > $resource['qty']) {
            $quantity_err = "Requested quantity exceeds available quantity.";
        } else {
            // Deduct the spent quantity from available quantity
            $newQuantity = $resource['qty'] - $quantity;
            $sqlUpdate = "UPDATE resourcesavailable SET qty = :newQuantity WHERE re_av_id = :re_av_id";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':newQuantity', $newQuantity, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':re_av_id', $resourceAvId, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // Insert spending action into historical table
            $operation = 'spending';
            $date = date('Y-m-d');
            $status = 'completed';
            $sqlInsert = "INSERT INTO historical (re_id, quantity, operation, date, status) 
                          VALUES (:re_id, :quantity, :operation, :date, :status)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->bindParam(':re_id', $_POST["re_id"], PDO::PARAM_INT);
            $stmtInsert->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmtInsert->bindParam(':operation', $operation);
            $stmtInsert->bindParam(':date', $date);
            $stmtInsert->bindParam(':status', $status);
            $stmtInsert->execute();

            // Redirect to list_resources_available.php page after spending
            header("Location: list_resources_available.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spend Resource</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Spend Resource: <?php echo $resource['re_name']; ?></h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $resourceAvId; ?>" method="post">
        <div class="form-group">
            <label>Available Quantity: <?php echo $resource['qty']; ?></label>
        </div>
        <div class="form-group">
            <input type="hidden" name="re_id" value="<?php echo $resource['re_id']; ?>">
            <label>Enter Quantity to Spend:</label>
            <input type="number" name="quantity" class="form-control <?php echo (!empty($quantity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo isset($quantity) ? $quantity : ''; ?>">
            <span class="invalid-feedback"><?php echo $quantity_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Export">
            <a href="list_resources_available.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
